<?php
// ollama_ui.php
?><!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>Ollama · Panel interactivo</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    :root { --border:#e5e7eb; --muted:#6b7280; --bg:#fafafa; --accent:#111827; }
    * { box-sizing: border-box; }
    body { margin: 24px; font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; line-height: 1.35; }
    h1 { margin: 0 0 12px; font-size: 22px; }
    .bar { display:flex; gap:12px; align-items:center; margin: 8px 0 16px; }
    .tabs { display:flex; gap:8px; flex-wrap: wrap; }
    .tab {
      border:1px solid var(--border); padding:8px 12px; border-radius:10px;
      background:#fff; cursor:pointer; user-select:none;
    }
    .tab.active { background:#111827; color:#fff; border-color:#111827; }
    .muted { color: var(--muted); }
    .row { display:flex; gap:18px; align-items: flex-start; flex-wrap: wrap; }
    .card {
      background:#fff; border:1px solid var(--border); border-radius:14px; padding:14px;
      box-shadow: 0 1px 2px rgba(0,0,0,0.03);
    }
    textarea, input[type="text"] {
      width:100%; border:1px solid var(--border); border-radius:10px; padding:10px; font: inherit;
      min-height: 120px;
    }
    .controls { display:flex; gap:10px; flex-wrap: wrap; align-items:center; }
    button {
      border:1px solid var(--accent); background:var(--accent); color:#fff; padding:10px 14px; border-radius:10px;
      cursor:pointer;
    }
    button.secondary { background:#fff; color:#111827; border-color:#111827; }
    button:disabled { opacity:.6; cursor:not-allowed; }
    .spinner{ width:18px;height:18px;border:2px solid #ddd;border-top-color:#111827;border-radius:50%;display:inline-block;animation:spin 1s linear infinite;vertical-align:middle;margin-right:8px}
    @keyframes spin{to{transform:rotate(360deg)}}
    pre.output {
      white-space: pre-wrap; word-wrap: break-word; min-height: 180px; max-height: 60vh; overflow:auto;
      margin:0; font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
    }
    .tiny { font-size:12px; }
    .grid { display:grid; grid-template-columns: repeat(2, minmax(220px, 1fr)); gap:10px; }
    .field { display:flex; flex-direction:column; gap:6px; }
    .pill { display:inline-block; padding:2px 8px; background:#eef2ff; border:1px solid #c7d2fe; color:#3730a3; border-radius:999px; font-size:12px; }
  </style>
</head>
<body>
  <h1>Ollama · Panel interactivo</h1>
  <div class="muted tiny">Se detectan modelos desde <code>http://127.0.0.1:11434/api/tags</code></div>

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
          <label for="system" class="muted">System (opcional) <span class="pill">/api/generate</span></label>
          <input id="system" type="text" placeholder="Eres un asistente conciso.">
        </div>
        <div class="field">
          <label for="temperature" class="muted">Temperature: <span id="tempVal">0.7</span></label>
          <input id="temperature" type="range" min="0" max="2" step="0.1" value="0.7" />
        </div>
      </div>

      <div class="controls" style="margin-top:12px;">
        <button id="runBtn">Generar</button>
        <button id="stopBtn" class="secondary" disabled>Detener</button>
        <span id="runStatus" class="muted"></span>
      </div>
    </div>

    <div class="card" style="flex:1 1 380px;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
        <strong>Salida</strong>
        <span id="tokenCount" class="tiny muted"></span>
      </div>
      <pre id="output" class="output"></pre>
    </div>
  </div>

  <script>
    const API_BASE = 'http://127.0.0.1:11434';
    const tabs = document.getElementById('modelsTabs');
    const status = document.getElementById('modelsStatus');
    const chosen = document.getElementById('chosen');

    const runBtn = document.getElementById('runBtn');
    const stopBtn = document.getElementById('stopBtn');
    const outputEl = document.getElementById('output');
    const runStatus = document.getElementById('runStatus');
    const temp = document.getElementById('temperature');
    const tempVal = document.getElementById('tempVal');
    const tokenCount = document.getElementById('tokenCount');

    let MODELS = [];
    let currentModel = null;
    let controller = null;
    let tokenCounter = 0;

    temp.addEventListener('input', () => tempVal.textContent = temp.value);

    async function loadModels() {
      try {
        const res = await fetch(API_BASE + '/api/tags', { cache: 'no-store' });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const data = await res.json();
        MODELS = data.models || [];
        if (MODELS.length === 0) throw new Error('Sin modelos');
        renderTabs(MODELS);
        status.style.display = 'none';
        tabs.style.display = 'flex';
        chosen.style.display = 'block';
      } catch (e) {
        status.innerHTML = 'No se pudieron cargar los modelos. ¿Ollama está corriendo en ' + API_BASE + '?';
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
      runBtn.disabled = isRunning || !currentModel;
      stopBtn.disabled = !isRunning;
    }

    function appendOut(text) {
      outputEl.textContent += text;
      outputEl.scrollTop = outputEl.scrollHeight;
    }

    function resetOut() {
      outputEl.textContent = '';
      tokenCounter = 0;
      tokenCount.textContent = '';
    }

    function updateTokens(n) {
      tokenCounter += n;
      tokenCount.textContent = tokenCounter ? `tokens ~ ${tokenCounter}` : '';
    }

    async function runGenerate() {
      if (!currentModel) return;
      const prompt = document.getElementById('prompt').value.trim();
      const system = document.getElementById('system').value.trim();
      if (!prompt) {
        alert('Escribe un prompt.');
        return;
      }

      setRunning(true);
      resetOut();
      runStatus.innerHTML = '<span class="spinner"></span>Generando…';

      controller = new AbortController();
      const payload = {
        model: currentModel,
        prompt,
        stream: true,
        options: { temperature: parseFloat(temp.value) }
      };
      if (system) payload.system = system;

      try {
        const res = await fetch(API_BASE + '/api/generate', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload),
          signal: controller.signal
        });
        if (!res.ok) throw new Error('HTTP ' + res.status);

        const reader = res.body.getReader();
        const decoder = new TextDecoder('utf-8');
        let buffer = '';

        while (true) {
          const { value, done } = await reader.read();
          if (done) break;
          buffer += decoder.decode(value, { stream: true });

          let idx;
          while ((idx = buffer.indexOf('\n')) >= 0) {
            const line = buffer.slice(0, idx).trim();
            buffer = buffer.slice(idx + 1);
            if (!line) continue;
            try {
              const obj = JSON.parse(line);
              if (obj.response) {
                appendOut(obj.response);
                updateTokens(1);
              }
              if (obj.done) break;
            } catch (e) {
              // línea incompleta o no JSON: ignorar
            }
          }
        }

        runStatus.textContent = 'Completado';
      } catch (e) {
        if (e.name === 'AbortError') {
          runStatus.textContent = 'Cancelado';
        } else {
          runStatus.textContent = 'Error: ' + e.message;
        }
      } finally {
        setRunning(false);
        controller = null;
      }
    }

    runBtn.addEventListener('click', runGenerate);
    stopBtn.addEventListener('click', () => {
      if (controller) controller.abort();
    });

    loadModels();
  </script>
</body>
</html>
