<?php
// ollama_ui.php — UI + API (status/chat) en un único archivo

/***********************
 * CONFIG
 ***********************/
const OLLAMA_BASE = 'http://127.0.0.1:11434'; // Cambia si Ollama está en otra IP/puerto
const DB_DSN      = 'mysql:host=127.0.0.1;dbname=chatdb;charset=utf8mb4';
const DB_USER     = 'root';  // tu usuario
const DB_PASS     = ''; // tu password

/***********************
 * HELPERS (PHP)
 ***********************/
function db(): ?PDO {
  static $pdo = null;
  if ($pdo !== null) return $pdo;
  try {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES => true, // facilita LIMIT y otras sentencias
    ]);
    return $pdo;
  } catch (Throwable $e) {
    return null; // sin DB -> modo sin memoria
  }
}

function uuidv4(): string {
  $data = random_bytes(16);
  $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
  $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
  return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

function ensureSchema(PDO $pdo): void {
  // Crea tablas si no existen (collation compatible)
  $pdo->exec("
    CREATE TABLE IF NOT EXISTS threads (
      id            BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
      uuid          CHAR(36) NOT NULL UNIQUE,
      title         VARCHAR(200) NULL,
      model         VARCHAR(100) NOT NULL,
      system_prompt TEXT NULL,
      keep_alive    VARCHAR(20) DEFAULT '10m',
      created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
  ");
  $pdo->exec("
    CREATE TABLE IF NOT EXISTS messages (
      id            BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
      thread_id     BIGINT UNSIGNED NOT NULL,
      role          ENUM('system','user','assistant') NOT NULL,
      content       MEDIUMTEXT NOT NULL,
      token_count   INT NULL,
      created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
      INDEX idx_thread_created (thread_id, created_at),
      CONSTRAINT fk_messages_thread
        FOREIGN KEY (thread_id) REFERENCES threads(id)
        ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
  ");
}

function getThreadByUUID(PDO $pdo, string $uuid): ?array {
  $st = $pdo->prepare("SELECT * FROM threads WHERE uuid=?");
  $st->execute([$uuid]);
  return $st->fetch() ?: null;
}

function createThread(PDO $pdo, string $model, ?string $systemPrompt, ?string $title, string $keep='10m'): array {
  $uuid = uuidv4();
  $st = $pdo->prepare("INSERT INTO threads (uuid,title,model,system_prompt,keep_alive) VALUES (?,?,?,?,?)");
  $st->execute([$uuid, $title, $model, $systemPrompt, $keep]);
  $id = (int)$pdo->lastInsertId();
  if ($systemPrompt && trim($systemPrompt) !== '') {
    addMessage($pdo, $id, 'system', $systemPrompt, null);
  }
  return ['id'=>$id,'uuid'=>$uuid];
}

function addMessage(PDO $pdo, int $threadId, string $role, string $content, ?int $tokens): void {
  $st = $pdo->prepare("INSERT INTO messages (thread_id,role,content,token_count) VALUES (?,?,?,?)");
  $st->execute([$threadId, $role, $content, $tokens]);
}

function getHistory(PDO $pdo, int $threadId, ?int $limit=null): array {
  $sql = "SELECT role, content FROM messages WHERE thread_id=:tid ORDER BY created_at ASC, id ASC";
  if ($limit !== null) {
    $limit = max(1, (int)$limit);
    $sql .= " LIMIT $limit";
  }
  $st = $pdo->prepare($sql);
  $st->bindValue(':tid', $threadId, PDO::PARAM_INT);
  $st->execute();
  return $st->fetchAll();
}

function http_json($code, $data) {
  http_response_code($code);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($data, JSON_UNESCAPED_UNICODE);
  exit;
}

function ollama_tags(): array {
  $url = OLLAMA_BASE . '/api/tags';
  $resp = @file_get_contents($url);
  if ($resp === false) throw new RuntimeException("No se pudo conectar con $url");
  $data = json_decode($resp, true);
  if (!is_array($data) || !isset($data['models'])) throw new RuntimeException("Respuesta inválida de /api/tags");
  return $data;
}

function ollama_chat(string $model, array $messages, string $keep='10m', array $options=[]): array {
  $payload = [
    'model'     => $model,
    'messages'  => $messages,
    'stream'    => false,
    'keep_alive'=> $keep,
  ];
  if (!empty($options)) $payload['options']=$options;

  $ch = curl_init(OLLAMA_BASE . '/api/chat');
  curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 180,
  ]);
  $res = curl_exec($ch);
  if ($res === false) {
    $err = curl_error($ch); curl_close($ch);
    throw new RuntimeException("Ollama error: $err");
  }
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  if ($code < 200 || $code >= 300) throw new RuntimeException("Ollama HTTP $code: $res");

  $data = json_decode($res, true);
  if (!is_array($data) || !isset($data['message']['content'])) throw new RuntimeException("Respuesta inválida /api/chat");
  return $data;
}

function ollama_generate_noctx(string $model, string $prompt, ?string $system=null, float $temperature=0.7): string {
  $payload = [
    'model'  => $model,
    'prompt' => $prompt,
    'stream' => false,
    'options'=> ['temperature'=>$temperature],
  ];
  if ($system && trim($system) !== '') $payload['system']=$system;

  $ch = curl_init(OLLAMA_BASE . '/api/generate');
  curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 180,
  ]);
  $res = curl_exec($ch);
  if ($res === false) {
    $err = curl_error($ch); curl_close($ch);
    throw new RuntimeException("Ollama error: $err");
  }
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  curl_close($ch);
  if ($code < 200 || $code >= 300) throw new RuntimeException("Ollama HTTP $code: $res");

  $data = json_decode($res, true);
  if (!is_array($data) || !isset($data['response'])) throw new RuntimeException("Respuesta inválida /api/generate");
  return (string)$data['response'];
}

/***********************
 * API ROUTES
 ***********************/
$action = $_GET['action'] ?? null;

if ($action === 'status') {
  $pdo = db();
  $dbConnected = $pdo instanceof PDO;
  $models = [];
  $err = null;

  if ($dbConnected) {
    try { ensureSchema($pdo); } catch (Throwable $e) { /* no fatal */ }
  }

  try {
    $models = ollama_tags()['models'];
  } catch (Throwable $e) {
    $err = $e->getMessage();
  }

  http_json(200, [
    'dbConnected' => $dbConnected,
    'ollamaOk'    => $err === null,
    'models'      => $models,
    'error'       => $err
  ]);
}

if ($action === 'chat' && $_SERVER['REQUEST_METHOD'] === 'POST') {
  $input = json_decode(file_get_contents('php://input'), true) ?? [];
  $model = $input['model']   ?? 'mistral:7b';
  $prompt= trim($input['prompt'] ?? '');
  $system= trim($input['system'] ?? '');
  $temp  = isset($input['temperature']) ? (float)$input['temperature'] : 0.7;
  $threadUuid = $input['thread_uuid'] ?? null;

  if ($prompt === '') http_json(400, ['error'=>'prompt vacío']);

  $pdo = db();
  $dbConnected = $pdo instanceof PDO;

  if (!$dbConnected) {
    // SIN CONTEXTO: /api/generate
    try {
      $resp = ollama_generate_noctx($model, $prompt, $system !== '' ? $system : null, $temp);
      http_json(200, [
        'dbConnected' => false,
        'memory'      => 'disabled',
        'assistant'   => $resp,
        'thread_uuid' => null
      ]);
    } catch (Throwable $e) {
      http_json(500, ['dbConnected'=>false, 'error'=>$e->getMessage()]);
    }
  }

  // CON DB: conversa con /api/chat y guarda historial
  try {
    ensureSchema($pdo);
    $pdo->beginTransaction();

    if ($threadUuid) {
      $thread = getThreadByUUID($pdo, $threadUuid);
      if (!$thread) {
        // si envían un UUID inexistente, crea uno nuevo
        $thread = null;
      }
    } else {
      $thread = null;
    }

    if (!$thread) {
      $created = createThread($pdo, $model, $system !== '' ? $system : null, 'Hilo ' . date('Y-m-d H:i'), '10m');
      $threadId = $created['id'];
      $threadUuid = $created['uuid'];
    } else {
      $threadId = (int)$thread['id'];
    }

    // Guarda turno del usuario
    addMessage($pdo, $threadId, 'user', $prompt, null);

    // Prepara historial completo (podrías limitarlo si crece mucho)
    $history = getHistory($pdo, $threadId, null);

    // Llama a /api/chat
    $resp = ollama_chat($model, $history, '10m', ['temperature'=>$temp]);
    $assistant = $resp['message']['content'] ?? '';

    // Guarda respuesta
    addMessage($pdo, $threadId, 'assistant', $assistant, null);

    $pdo->commit();

    http_json(200, [
      'dbConnected' => true,
      'memory'      => 'enabled',
      'assistant'   => $assistant,
      'thread_uuid' => $threadUuid
    ]);

  } catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
    http_json(500, ['dbConnected'=>true, 'error'=>$e->getMessage()]);
  }
}

/***********************
 * UI (HTML)
 ***********************/
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>Ollama · Panel interactivo (con memoria si hay DB)</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    :root { --border:#e5e7eb; --muted:#6b7280; --ok:#0f766e; --warn:#92400e; --bg:#fafafa; --accent:#111827; }
    * { box-sizing: border-box; }
    body { margin: 24px; font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; line-height: 1.35; background:#fff; }
    h1 { margin: 0 0 12px; font-size: 22px; }
    .bar { display:flex; gap:12px; align-items:center; margin: 8px 0 16px; flex-wrap:wrap; }
    .badge { display:inline-flex; align-items:center; gap:6px; padding:6px 10px; border-radius:999px; border:1px solid var(--border); font-size:12px; }
    .ok { color:#065f46; background:#ecfdf5; border-color:#a7f3d0; }
    .warn { color:#7c2d12; background:#fffbeb; border-color:#fcd34d; }
    .tabs { display:flex; gap:8px; flex-wrap: wrap; }
    .tab { border:1px solid var(--border); padding:8px 12px; border-radius:10px; background:#fff; cursor:pointer; user-select:none; }
    .tab.active { background:#111827; color:#fff; border-color:#111827; }
    .muted { color: var(--muted); }
    .row { display:flex; gap:18px; align-items: flex-start; flex-wrap: wrap; }
    .card { background:#fff; border:1px solid var(--border); border-radius:14px; padding:14px; box-shadow: 0 1px 2px rgba(0,0,0,0.03); }
    textarea, input[type="text"] { width:100%; border:1px solid var(--border); border-radius:10px; padding:10px; font: inherit; min-height: 120px; }
    .controls { display:flex; gap:10px; flex-wrap: wrap; align-items:center; }
    button { border:1px solid var(--accent); background:var(--accent); color:#fff; padding:10px 14px; border-radius:10px; cursor:pointer; }
    button.secondary { background:#fff; color:#111827; border-color:#111827; }
    button:disabled { opacity:.6; cursor:not-allowed; }
    .spinner{ width:18px;height:18px;border:2px solid #ddd;border-top-color:#111827;border-radius:50%;display:inline-block;animation:spin 1s linear infinite;vertical-align:middle;margin-right:8px }
    @keyframes spin{to{transform:rotate(360deg)}}
    pre.output { white-space: pre-wrap; word-wrap: break-word; min-height: 220px; max-height: 60vh; overflow:auto; margin:0; font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; }
    .tiny { font-size:12px; }
    .grid { display:grid; grid-template-columns: repeat(2, minmax(220px, 1fr)); gap:10px; }
    .field { display:flex; flex-direction:column; gap:6px; }
    .pill { display:inline-block; padding:2px 8px; background:#eef2ff; border:1px solid #c7d2fe; color:#3730a3; border-radius:999px; font-size:12px; }
  </style>
</head>
<body>
  <h1>Ollama · Panel interactivo</h1>
  <div class="bar">
    <div id="dbBadge" class="badge warn">Memoria: comprobando…</div>
    <div id="ollamaBadge" class="badge warn">Ollama: comprobando…</div>
  </div>

  <div class="muted tiny">Modelos desde <code><?php echo htmlspecialchars(OLLAMA_BASE); ?>/api/tags</code> · Memoria del hilo activada solo si hay conexión a DB.</div>

  <div id="modelsCard" class="card" style="margin-top:12px;">
    <div id="modelsStatus"><span class="spinner"></span>Cargando modelos…</div>
    <div id="modelsTabs" class="tabs" style="margin-top:10px; display:none;"></div>
    <div id="chosen" class="tiny muted" style="margin-top:6px; display:none;"></div>
  </div>

  <div class="row" style="margin-top:16px;">
    <div class="card" style="flex:1 1 380px;">
      <div class="field">
        <label for="prompt"><strong>Prompt</strong></label>
        <textarea id="prompt" placeholder="Escribe tu prompt aquí… Por ejemplo: Explica la recursividad con un ejemplo en PHP."></textarea>
      </div>

      <div class="grid" style="margin-top:12px;">
        <div class="field">
          <label for="system" class="muted">System (opcional) <span class="pill">se usa al crear hilo</span></label>
          <input id="system" type="text" placeholder="Eres un asistente conciso.">
        </div>
        <div class="field">
          <label for="temperature" class="muted">Temperature: <span id="tempVal">0.7</span></label>
          <input id="temperature" type="range" min="0" max="2" step="0.1" value="0.7" />
        </div>
      </div>

      <div class="controls" style="margin-top:12px;">
        <button id="runBtn">Enviar</button>
        <button id="newThreadBtn" class="secondary">Nuevo hilo</button>
        <span id="runStatus" class="muted"></span>
      </div>
    </div>

    <div class="card" style="flex:1 1 380px;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
        <strong>Salida</strong>
        <span id="threadInfo" class="tiny muted"></span>
      </div>
      <pre id="output" class="output"></pre>
    </div>
  </div>

  <script>
    const API_STATUS = '?action=status';
    const API_CHAT   = '?action=chat';

    const dbBadge = document.getElementById('dbBadge');
    const ollamaBadge = document.getElementById('ollamaBadge');

    const tabs = document.getElementById('modelsTabs');
    const status = document.getElementById('modelsStatus');
    const chosen = document.getElementById('chosen');

    const runBtn = document.getElementById('runBtn');
    const newThreadBtn = document.getElementById('newThreadBtn');
    const outputEl = document.getElementById('output');
    const runStatus = document.getElementById('runStatus');
    const temp = document.getElementById('temperature');
    const tempVal = document.getElementById('tempVal');
    const threadInfo = document.getElementById('threadInfo');

    const promptEl = document.getElementById('prompt');
    const systemEl = document.getElementById('system');

    let MODELS = [];
    let currentModel = null;
    let dbConnected = false;
    let ollamaOk = false;
    let threadUUID = null; // solo si hay DB
    temp.addEventListener('input', () => tempVal.textContent = temp.value);

    function setBadge(el, ok, labelOk, labelWarn) {
      el.classList.remove('ok','warn');
      el.classList.add(ok ? 'ok' : 'warn');
      el.textContent = ok ? labelOk : labelWarn;
    }

    function append(text) {
      outputEl.textContent += text;
      outputEl.scrollTop = outputEl.scrollHeight;
    }
    function setOutput(text) { outputEl.textContent = text; }

    async function loadStatus() {
      const res = await fetch(API_STATUS, { cache:'no-store' });
      const data = await res.json();
      dbConnected = !!data.dbConnected;
      ollamaOk = !!data.ollamaOk;
      MODELS = data.models || [];

      setBadge(dbBadge, dbConnected,
        'Memoria: activada (DB OK)',
        'Memoria: desactivada (sin DB)'
      );
      setBadge(ollamaBadge, ollamaOk,
        'Ollama: conectado',
        'Ollama: desconectado'
      );

      if (MODELS.length > 0) {
        renderTabs(MODELS);
        status.style.display = 'none';
        tabs.style.display = 'flex';
        chosen.style.display = 'block';
      } else {
        status.innerHTML = 'No se pudieron cargar los modelos. ¿Ollama está corriendo?';
      }
    }

    function renderTabs(models) {
      tabs.innerHTML = '';
      models.forEach((m, i) => {
        const btn = document.createElement('button');
        btn.className = 'tab' + (i === 0 ? ' active' : '');
        btn.textContent = m.name;
        btn.title = (m.details?.parameter_size || '') + ' · ' + (m.details?.family || m.details?.families?.[0] || '');
        btn.addEventListener('click', () => selectModel(i));
        tabs.appendChild(btn);
      });
      selectModel(0);
    }

    function selectModel(i) {
      [...tabs.children].forEach((el, idx) => el.classList.toggle('active', idx === i));
      currentModel = MODELS[i]?.name || null;
      const fam = MODELS[i]?.details?.family || MODELS[i]?.details?.families?.[0] || '';
      const params = MODELS[i]?.details?.parameter_size || '';
      chosen.textContent = `Modelo seleccionado: ${currentModel} ${params ? '· ' + params : ''} ${fam ? '· ' + fam : ''}`;
    }

    function setRunning(isRunning) {
      runBtn.disabled = isRunning || !currentModel || !ollamaOk;
      newThreadBtn.disabled = isRunning;
    }

    newThreadBtn.addEventListener('click', () => {
      threadUUID = null;
      threadInfo.textContent = 'Hilo: (nuevo)';
      setOutput('');
    });

    async function sendChat() {
      if (!currentModel) { alert('Selecciona un modelo'); return; }
      if (!ollamaOk) { alert('Ollama no está disponible'); return; }
      const prompt = promptEl.value.trim();
      const system = systemEl.value.trim();
      if (!prompt) { alert('Escribe un prompt'); return; }

      setRunning(true);
      runStatus.innerHTML = '<span class="spinner"></span>Enviando…';

      try {
        const body = {
          model: currentModel,
          prompt: prompt,
          system: system, // solo se usa al crear hilo
          temperature: parseFloat(temp.value),
          thread_uuid: dbConnected ? threadUUID : null
        };
        const res = await fetch(API_CHAT, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(body)
        });
        const data = await res.json();
        if (!res.ok) {
          throw new Error(data?.error || ('HTTP ' + res.status));
        }

        // Modo sin DB: no persiste hilo
        if (!data.dbConnected) {
          threadInfo.textContent = 'Hilo: sin memoria (DB no conectada)';
        } else {
          // Modo con DB: guarda/actualiza UUID de hilo
          threadUUID = data.thread_uuid || threadUUID;
          threadInfo.textContent = 'Hilo: ' + threadUUID;
        }

        append((outputEl.textContent ? '\n\n' : '') + '🧠 Respuesta:\n' + data.assistant);

        runStatus.textContent = data.dbConnected ? 'Completado (memoria ON)' : 'Completado (memoria OFF)';
      } catch (e) {
        runStatus.textContent = 'Error: ' + e.message;
      } finally {
        setRunning(false);
      }
    }

    runBtn.addEventListener('click', sendChat);
    loadStatus();
  </script>
</body>
</html>
