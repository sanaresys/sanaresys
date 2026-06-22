<x-filament-panels::page>
    @php
        $toneClasses = [
            'success' => 'b-badge-success',
            'warning' => 'b-badge-warning',
            'danger'  => 'b-badge-danger',
            'neutral' => 'b-badge-neutral',
        ];
        $openInvoice           = $billing['open_invoice'];
        $hasOpenInvoice        = (bool) $openInvoice;
        $canManage             = $billing['capabilities']['can_manage'];
        $canPay                = $billing['capabilities']['can_pay'];
        $canManageCancellation = $billing['capabilities']['can_manage_cancellation'];
        $moduleIntervalDefaults = collect($billing['modules'])
            ->mapWithKeys(fn ($m) => [(string) $m['id'] => $m['default_interval']])->all();
        $moduleMeta = collect($billing['modules'])
            ->mapWithKeys(fn ($m) => [(string) $m['id'] => ['name' => $m['name'], 'status_label' => $m['status_label']]])->all();
    @endphp

    @once
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700;800;900&family=Geist+Mono:wght@400;500&display=swap');

        .b {
            --font:    'Geist', system-ui, sans-serif;
            --mono:    'Geist Mono', monospace;
            --bg:      #f7f7f5;
            --surface: #ffffff;
            --surf-b:  rgba(0,0,0,0.06);
            --soft:    #f2f2f0;
            --soft-b:  rgba(0,0,0,0.07);
            --line:    rgba(0,0,0,0.07);
            --ink:     #111110;
            --muted:   #6f6e69;
            --subtle:  #a8a8a2;
            --accent:  #111110;
            --acc-fg:  #ffffff;
            --acc-h:   #2e2e2c;
            --blue:    #1a56db;
            --blue-s:  rgba(26,86,219,0.07);
            --blue-b:  rgba(26,86,219,0.18);
            --blue-fg: #fff;
            --amber:     #92400e;
            --amber-bg:  #fffbeb;
            --amber-b:   rgba(217,119,6,0.22);
            --green:     #065f46;
            --green-bg:  #f0fdf4;
            --green-b:   rgba(16,185,129,0.22);
            --red:       #9f1239;
            --red-bg:    #fff1f2;
            --red-b:     rgba(244,63,94,0.22);
            --sel-bg:  rgba(17,17,16,0.04);
            --sel-b:   rgba(17,17,16,0.15);
            --r-xs: 0.45rem;
            --r-sm: 0.7rem;
            --r:    1.05rem;
            --r-lg: 1.5rem;
            --r-xl: 2rem;
            --sh-sm: 0 1px 2px rgba(0,0,0,0.05),0 0 0 1px rgba(0,0,0,0.04);
            --sh:    0 4px 16px rgba(0,0,0,0.07),0 1px 4px rgba(0,0,0,0.04);
            --sh-lg: 0 16px 48px rgba(0,0,0,0.10),0 4px 12px rgba(0,0,0,0.05);
            --pw: 25rem;
            font-family: var(--font);
            color: var(--ink);
            background: var(--bg);
        }

        :is(html.dark,.dark) .b {
            --bg:      #111110;
            --surface: #1a1a18;
            --surf-b:  rgba(255,255,255,0.07);
            --soft:    rgba(255,255,255,0.04);
            --soft-b:  rgba(255,255,255,0.07);
            --line:    rgba(255,255,255,0.07);
            --ink:     #f0f0ee;
            --muted:   #a0a09a;
            --subtle:  #5a5a55;
            --accent:  #f0f0ee;
            --acc-fg:  #111110;
            --acc-h:   #d0d0cc;
            --blue:    #5b8def;
            --blue-s:  rgba(91,141,239,0.08);
            --blue-b:  rgba(91,141,239,0.20);
            --blue-fg: #111110;
            --amber:   #fbbf24;
            --amber-bg:rgba(251,191,36,0.07);
            --amber-b: rgba(251,191,36,0.20);
            --green:   #34d399;
            --green-bg:rgba(52,211,153,0.07);
            --green-b: rgba(52,211,153,0.20);
            --red:     #fb7185;
            --red-bg:  rgba(251,113,133,0.07);
            --red-b:   rgba(251,113,133,0.20);
            --sel-bg:  rgba(255,255,255,0.05);
            --sel-b:   rgba(255,255,255,0.14);
            --sh-sm:   0 1px 3px rgba(0,0,0,0.30);
            --sh:      0 4px 18px rgba(0,0,0,0.36);
            --sh-lg:   0 18px 52px rgba(0,0,0,0.46);
        }

        [x-cloak]       { display: none !important; }

        /* ── Stage layout ───────────────────────────────────── */
        .b { min-height: 100%; }

        .b-stage {
            display: flex;
            align-items: flex-start;
            min-height: 100%;
        }

        /* LIST: starts centered, slides left when open */
        .b-list {
            flex: 1 1 auto;
            min-width: 0;
            max-width: 44rem;
            margin: 0 auto;
            padding: 2rem 1.25rem 4rem;
            transition: max-width 360ms cubic-bezier(0.32,0,0.16,1),
                        margin    360ms cubic-bezier(0.32,0,0.16,1);
        }
        .b-stage.open .b-list {
            max-width: 100%;
            margin-left: 0;
            margin-right: 0;
        }

        /* PANEL COL: hidden (width:0) → visible */
        .b-panel-col {
            flex: 0 0 0;
            width: 0;
            overflow: hidden;
            align-self: stretch;
            position: sticky;
            top: 0;
            height: 100vh;
            transition: flex-basis 360ms cubic-bezier(0.32,0,0.16,1),
                        width      360ms cubic-bezier(0.32,0,0.16,1);
        }
        .b-stage.open .b-panel-col {
            flex-basis: var(--pw);
            width: var(--pw);
        }
        .b-panel-frame {
            width: var(--pw);
            height: 100%;
            display: flex;
            flex-direction: column;
            background: var(--surface);
            border-left: 1px solid var(--line);
            box-shadow: -8px 0 40px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        /* Mobile: fixed drawer */
        @media (max-width: 1023px) {
            .b-stage        { display: block; }
            .b-list         { max-width: 100% !important; margin: 0 !important; padding: 1.25rem 1rem 3rem !important; }
            .b-panel-col    { position: fixed; inset: 0 0 0 auto; z-index: 40; height: 100%; flex-basis: auto !important; width: 0 !important; overflow: hidden; }
            .b-stage.open .b-panel-col { width: min(92vw, var(--pw)) !important; }
            .b-panel-frame  { width: min(92vw, var(--pw)); box-shadow: var(--sh-lg), -1px 0 0 var(--line); }
        }

        /* ── Panel internals ────────────────────────────────── */
        .b-panel-head {
            flex-shrink: 0;
            padding: 1.2rem 1.4rem;
            border-bottom: 1px solid var(--line);
            background: var(--surface);
        }
        .b-panel-body {
            flex: 1 1 auto;
            overflow-y: auto;
            padding: 1.2rem 1.4rem 3rem;
            overscroll-behavior: contain;
        }

        /* ── Cards ──────────────────────────────────────────── */
        .b-card { background: var(--surface); border: 1px solid var(--surf-b); border-radius: var(--r-lg); box-shadow: var(--sh); }
        .b-soft { background: var(--soft); border: 1px solid var(--soft-b); border-radius: var(--r); }

        /* ── Table ──────────────────────────────────────────── */
        .b-table { background: var(--surface); border: 1px solid var(--surf-b); border-radius: var(--r-lg); box-shadow: var(--sh); overflow: hidden; }
        .b-thead { background: var(--soft); padding: 0.5rem 1rem; border-bottom: 1px solid var(--line); }
        .b-tr {
            display: grid;
            grid-template-columns: 1fr auto auto;
            align-items: center;
            gap: 0.75rem;
            padding: 0.8rem 1rem;
            border-bottom: 1px solid var(--line);
            cursor: pointer;
            transition: background 120ms;
            user-select: none;
        }
        .b-tr:last-child { border-bottom: none; }
        .b-tr:hover { background: var(--soft); }
        .b-tr.sel { background: var(--sel-bg); outline: 1.5px solid var(--sel-b); outline-offset: -1.5px; }
        .b-tr.sel .b-dots { background: var(--accent); color: var(--acc-fg); border-color: transparent; }

        /* Dots button */
        .b-dots {
            display: inline-flex; align-items: center; justify-content: center;
            width: 1.9rem; height: 1.9rem; border-radius: var(--r-xs);
            border: 1px solid var(--soft-b); background: var(--soft);
            color: var(--muted); cursor: pointer; flex-shrink: 0;
            transition: background 120ms, color 120ms, border-color 120ms;
        }
        .b-dots:hover { background: var(--surface); color: var(--ink); border-color: var(--line); box-shadow: var(--sh-sm); }

        /* ── Badges ─────────────────────────────────────────── */
        .b-badge { display: inline-flex; align-items: center; border-radius: 9999px; border: 1px solid transparent; padding: 0.17rem 0.58rem; font-size: 0.67rem; font-weight: 600; letter-spacing: 0.02em; white-space: nowrap; }
        .b-badge-success { color: var(--green); background: var(--green-bg); border-color: var(--green-b); }
        .b-badge-warning { color: var(--amber); background: var(--amber-bg); border-color: var(--amber-b); }
        .b-badge-danger  { color: var(--red);   background: var(--red-bg);   border-color: var(--red-b); }
        .b-badge-neutral { color: var(--muted); background: var(--soft);     border-color: var(--soft-b); }

        /* ── Buttons ─────────────────────────────────────────── */
        .b-btn { display: inline-flex; align-items: center; gap: 0.4rem; font-family: var(--font); font-size: 0.79rem; font-weight: 600; padding: 0.52rem 0.95rem; border-radius: var(--r-sm); border: 1px solid transparent; cursor: pointer; transition: background 140ms, box-shadow 140ms, transform 80ms; text-decoration: none; }
        .b-btn:active { transform: scale(0.97); }
        .b-btn-ghost   { background: var(--soft); border-color: var(--soft-b); color: var(--muted); }
        .b-btn-ghost:hover { background: var(--surface); color: var(--ink); border-color: var(--line); }
        .b-btn-primary { background: var(--accent); color: var(--acc-fg); }
        .b-btn-primary:hover { background: var(--acc-h); box-shadow: 0 3px 12px rgba(0,0,0,0.18); }
        .b-btn-blue    { background: var(--blue);   color: var(--blue-fg); }
        .b-btn-blue:hover { opacity: .88; box-shadow: 0 3px 12px rgba(26,86,219,0.26); }
        .b-btn-amber   { background: #d97706; color: #fff; }
        .b-btn-amber:hover { background: #b45309; }
        .b-icon-btn { display: inline-flex; align-items: center; justify-content: center; width: 2.1rem; height: 2.1rem; border-radius: var(--r-xs); border: 1px solid var(--soft-b); background: var(--soft); color: var(--muted); cursor: pointer; transition: all 130ms; }
        .b-icon-btn:hover { background: var(--surface); color: var(--ink); }

        /* ── Alerts ─────────────────────────────────────────── */
        .b-alert { border: 1px solid; border-radius: var(--r); padding: 0.8rem 0.95rem; }
        .b-alert-warning { background: var(--amber-bg); border-color: var(--amber-b); color: var(--amber); }
        .b-alert-success { background: var(--green-bg); border-color: var(--green-b); color: var(--green); }
        .b-alert-danger  { background: var(--red-bg);   border-color: var(--red-b);   color: var(--red); }

        /* ── Misc helpers ───────────────────────────────────── */
        .b-label { font-size: 0.62rem; font-weight: 700; letter-spacing: 0.11em; text-transform: uppercase; color: var(--subtle); }
        .b-val   { font-size: 0.87rem; font-weight: 700; color: var(--ink); margin-top: 0.22rem; line-height: 1.25; }
        .b-val-sm{ font-size: 0.78rem; font-weight: 600; color: var(--ink); margin-top: 0.18rem; }
        .b-mono  { font-family: var(--mono); }
        .b-kv    { background: var(--soft); border: 1px solid var(--soft-b); border-radius: var(--r); padding: 0.7rem 0.85rem; }
        .b-hr    { border: none; border-top: 1px solid var(--line); margin: 0; }
        .b-sec-label { font-size: 0.65rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase; color: var(--subtle); }
        .b-sec-head { display: flex; align-items: center; justify-content: space-between; padding: 0 0.15rem; margin-bottom: 0.45rem; gap: 0.75rem; }
        .b-sec-note { font-size: 0.64rem; font-weight: 600; color: var(--muted); }
        .b-mono-big  { font-family: var(--mono); font-size: 2.2rem; font-weight: 500; letter-spacing: -0.03em; color: var(--ink); }
        .b-panel-eyebrow { font-size: 0.6rem; font-weight: 700; letter-spacing: 0.13em; text-transform: uppercase; color: var(--subtle); }
        .b-panel-title   { font-size: 1.05rem; font-weight: 800; color: var(--ink); letter-spacing: -0.02em; margin-top: 0.25rem; }
        .b-panel-sub     { font-size: 0.73rem; color: var(--muted); margin-top: 0.15rem; line-height: 1.5; }

        /* Option selector */
        .b-opt { width: 100%; text-align: left; background: transparent; border: 1px solid var(--line); border-radius: var(--r); padding: 0.8rem 0.95rem; cursor: pointer; font-family: var(--font); transition: background 130ms, border-color 130ms; display: flex; align-items: flex-start; justify-content: space-between; gap: 0.75rem; }
        .b-opt:hover { background: var(--soft); }
        .b-opt.on { border-color: var(--sel-b); background: var(--sel-bg); }

        /* History table */
        .b-history {
            background: var(--surface);
            border: 1px solid var(--surf-b);
            border-radius: var(--r-lg);
            overflow: hidden;
            box-shadow: var(--sh-sm);
        }
        .b-history-head,
        .b-history-row {
            display: grid;
            grid-template-columns: minmax(0, 1.45fr) minmax(0, 0.75fr) minmax(0, 0.75fr) minmax(0, 1.05fr);
            gap: 0.6rem;
            align-items: center;
        }
        .b-history-head {
            padding: 0.5rem 0.85rem;
            background: var(--soft);
            border-bottom: 1px solid var(--line);
        }
        .b-history-row {
            padding: 0.62rem 0.85rem;
            border-bottom: 1px solid var(--line);
            transition: background 120ms;
        }
        .b-history-row:last-child { border-bottom: none; }
        .b-history-row:hover { background: var(--soft); }
        .b-history-cell { min-width: 0; }
        .b-history-main-id { font-size: 0.75rem; font-weight: 600; color: var(--ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .b-history-main-kind { font-size: 0.66rem; color: var(--muted); margin-top: 0.08rem; }
        .b-history-empty { padding: 2rem 1rem; text-align: center; font-size: 0.76rem; color: var(--subtle); }

        @media (max-width: 760px) {
            .b-history-head { display: none; }
            .b-history-row {
                grid-template-columns: 1fr;
                gap: 0.42rem;
                padding: 0.78rem 0.85rem;
            }
            .b-history-cell {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 0.65rem;
            }
            .b-history-cell::before {
                content: attr(data-label);
                font-size: 0.61rem;
                font-weight: 700;
                letter-spacing: 0.08em;
                text-transform: uppercase;
                color: var(--subtle);
            }
            .b-history-cell.main {
                display: block;
            }
            .b-history-cell.main::before {
                display: none;
            }
        }

        /* Invoice item */
        .b-iitem { background: var(--soft); border: 1px solid var(--soft-b); border-radius: var(--r); padding: 0.8rem 0.95rem; display: flex; align-items: flex-start; justify-content: space-between; gap: 0.75rem; }

        /* Pending bar */
        .b-pbar { width: 100%; text-align: left; background: var(--amber-bg); border: 1px solid var(--amber-b); border-radius: var(--r); padding: 0.8rem 0.95rem; display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; cursor: pointer; transition: opacity 140ms; }
        .b-pbar:hover { opacity: .8; }

        /* Pending dot */
        .b-pdot { width: 0.4rem; height: 0.4rem; border-radius: 9999px; background: #d97706; flex-shrink: 0; box-shadow: 0 0 0 3px rgba(217,119,6,.18); }

        /* Backdrop */
        .b-backdrop { position: fixed; inset: 0; z-index: 30; background: rgba(0,0,0,0.30); backdrop-filter: blur(4px); }

        /* Fade in */
        @keyframes b-in { from { opacity:0; transform:translateY(5px); } to { opacity:1; transform:none; } }
        .b-fade { animation: b-in .3s ease both; }

        /* Header */
        .b-strip { background: var(--surface); border: 1px solid var(--surf-b); border-radius: var(--r-xl); box-shadow: var(--sh); padding: 1.25rem 1.5rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1.25rem; }

        #bpp { min-height: 48px; }
    </style>
    @endonce

    <div
        x-data="billingWs({
            moduleIntervals: @js($moduleIntervalDefaults),
            moduleMeta: @js($moduleMeta),
            initialPanel: @js($billing['initial_panel'] ?? null),
            invCfg: @js($openInvoice ? [
                'orderUrl'    => $openInvoice['order_url'],
                'captureUrl'  => $openInvoice['capture_url'],
                'containerId' => 'bpp',
                'messageId'   => 'bpm',
            ] : null),
        })"
        x-init="init()"
        x-effect="lockScroll()"
        class="b"
    >
        {{-- Backdrop (mobile) --}}
        <div x-cloak x-show="open" x-transition.opacity.duration.200ms
             class="b-backdrop lg:hidden" @click="close()"></div>

        {{-- Flash --}}
        @if(session('status'))
            <div class="b-alert b-alert-success b-fade" style="margin:1rem 1.25rem 0">{{ session('status') }}</div>
        @endif
        @if(session('error'))
            <div class="b-alert b-alert-danger b-fade" style="margin:1rem 1.25rem 0">{{ session('error') }}</div>
        @endif

        {{-- ═══════════════════════════ STAGE ═════════════════ --}}
        <div class="b-stage" :class="open?'open':''">

            {{-- ── LIST COLUMN ─────────────────────────────── --}}
            <div class="b-list b-fade">

                {{-- Top strip --}}
                <div class="b-strip">
                    <div>
                        <p class="b-sec-label" style="margin-bottom:0.15rem">Billing</p>
                        <h1 style="font-size:1.25rem;font-weight:800;letter-spacing:-0.025em;line-height:1.2;color:var(--ink)">
                            {{ $billing['centro']['name'] }}
                        </h1>
                    </div>
                    <div style="display:flex;align-items:center;gap:0.5rem;flex-wrap:wrap">
                        @if($hasOpenInvoice)
                            <button type="button" @click="show('invoice')"
                                style="display:inline-flex;align-items:center;gap:0.4rem;font-family:var(--font);font-size:0.7rem;font-weight:600;padding:0.4rem 0.75rem;border-radius:9999px;background:var(--amber-bg);border:1px solid var(--amber-b);color:var(--amber);cursor:pointer;transition:opacity 140ms"
                                :style="panel==='invoice'?'opacity:.55':''"
                            >
                                <span class="b-pdot"></span>Pago pendiente
                            </button>
                        @endif
                        <button type="button" @click="show('history')" class="b-btn b-btn-ghost" style="font-size:0.72rem;padding:0.4rem 0.75rem">
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            Historial
                        </button>
                        <a href="{{ $billing['admin_url'] }}" class="b-btn b-btn-ghost" style="font-size:0.72rem;padding:0.4rem 0.75rem">
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            Volver
                        </a>
                    </div>
                </div>

                {{-- ── PLAN ACTIVO ─────────────────────────── --}}
                <div style="margin-bottom:1.25rem">
                    <div class="b-sec-head">
                        <span class="b-sec-label">Plan activo</span>
                        <span class="b-sec-note">1 registro</span>
                    </div>
                    <div class="b-table">
                        <div class="b-thead" style="display:grid;grid-template-columns:1fr auto auto;gap:0.75rem">
                            <span class="b-sec-label">Suscripción</span>
                            <span class="b-sec-label">Estado</span>
                            <span class="b-sec-label" style="text-align:right">Acción</span>
                        </div>
                        <div class="b-tr" :class="panel==='plan'?'sel':''" @click="show('plan')">
                            <div>
                                <p style="font-size:0.875rem;font-weight:600;color:var(--ink)">{{ $billing['tenant']['plan_label'] }}</p>
                                <p style="font-size:0.7rem;color:var(--muted);margin-top:0.08rem">{{ $billing['tenant']['interval_label'] }}</p>
                            </div>
                            <span class="b-badge {{ $toneClasses[$billing['tenant']['status_tone']] ?? $toneClasses['neutral'] }}">{{ $billing['tenant']['status_label'] }}</span>
                            <button class="b-dots" type="button" @click.stop="show('plan')" aria-label="Ver plan">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" stroke-linecap="round"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- ── MÓDULOS ─────────────────────────────── --}}
                <div>
                    <div class="b-sec-head">
                        <span class="b-sec-label">Módulos</span>
                        <span class="b-sec-note">{{ count($billing['modules']) }} disponibles</span>
                    </div>
                    <div class="b-table">
                        <div class="b-thead" style="display:grid;grid-template-columns:1fr auto auto;gap:0.75rem">
                            <span class="b-sec-label">Módulo</span>
                            <span class="b-sec-label">Estado</span>
                            <span class="b-sec-label" style="text-align:right">Acción</span>
                        </div>
                        @foreach($billing['modules'] as $module)
                            @php($needsAttn = in_array($module['status'],['pending','past_due','grace'],true)||$module['is_current_open_invoice'])
                            <div class="b-tr" :class="isModule('{{ $module['id'] }}')?'sel':''" @click="showMod('{{ $module['id'] }}')">
                                <div class="min-w-0">
                                    <p style="font-size:0.875rem;font-weight:600;color:var(--ink);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $module['name'] }}</p>
                                    <p style="font-size:0.69rem;color:{{ $needsAttn ? 'var(--amber)' : 'var(--muted)' }};margin-top:0.06rem">
                                        {{ $needsAttn ? 'Requiere atención' : $module['row_summary'] }}
                                    </p>
                                </div>
                                <span class="b-badge {{ $toneClasses[$module['status_tone']] ?? $toneClasses['neutral'] }}">{{ $module['status_label'] }}</span>
                                <button class="b-dots" type="button" @click.stop="showMod('{{ $module['id'] }}')" aria-label="Ver {{ $module['name'] }}">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" stroke-linecap="round"/></svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>{{-- /b-list --}}

            {{-- ── PANEL COLUMN ─────────────────────────────── --}}
            <div class="b-panel-col">
                <div class="b-panel-frame" x-cloak x-show="open"
                     x-transition:enter="transition-opacity duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100">

                    {{-- Head --}}
                    <div class="b-panel-head">
                        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:0.75rem">
                            <div class="min-w-0">
                                <p class="b-panel-eyebrow" x-text="eye()"></p>
                                <h2 class="b-panel-title" x-text="ttl()"></h2>
                                <p class="b-panel-sub" x-text="sub()"></p>
                            </div>
                            <button type="button" @click="close()" class="b-icon-btn" aria-label="Cerrar">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18 18 6M6 6l12 12" stroke-linecap="round"/></svg>
                            </button>
                        </div>
                    </div>

                    {{-- Body --}}
                    <div class="b-panel-body">

                        {{-- HISTORIAL --}}
                        <section x-cloak x-show="panel==='history'" x-transition.opacity.duration.150ms style="display:flex;flex-direction:column;gap:0.75rem">
                            <p style="font-size:0.76rem;color:var(--muted);line-height:1.6">Revisa tus pagos anteriores con un formato más claro por factura, monto, estado y cobertura.</p>
                            <div class="b-history">
                                <div class="b-history-head">
                                    <span class="b-label">Factura</span>
                                    <span class="b-label">Monto</span>
                                    <span class="b-label">Estado</span>
                                    <span class="b-label">Cubre</span>
                                </div>
                                @forelse($billing['history'] as $h)
                                    <div class="b-history-row">
                                        <div class="b-history-cell main">
                                            <p class="b-mono b-history-main-id">{{ $h['public_id'] }}</p>
                                            <p class="b-history-main-kind">{{ $h['kind_label'] }}</p>
                                        </div>
                                        <div class="b-history-cell b-mono" data-label="Monto" style="font-size:0.8rem;font-weight:700;color:var(--ink)">
                                            {{ $h['total'] }}
                                        </div>
                                        <div class="b-history-cell" data-label="Estado">
                                            <span class="b-badge {{ $toneClasses[$h['status_tone']] ?? $toneClasses['neutral'] }}">{{ $h['status_label'] }}</span>
                                        </div>
                                        <div class="b-history-cell" data-label="Cubre" style="font-size:0.69rem;color:var(--muted)">
                                            {{ $h['cover_range'] }}
                                        </div>
                                    </div>
                                @empty
                                    <div class="b-history-empty">Aún no hay movimientos.</div>
                                @endforelse
                            </div>
                        </section>

                        {{-- PLAN --}}
                        <section x-cloak x-show="panel==='plan'" x-transition.opacity.duration.150ms style="display:flex;flex-direction:column;gap:0.7rem">
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem">
                                <div class="b-kv"><p class="b-label">Plan</p><p class="b-val-sm">{{ $billing['tenant']['plan_label'] }}</p></div>
                                <div class="b-kv"><p class="b-label">Ciclo</p><p class="b-val-sm">{{ $billing['tenant']['interval_label'] }}</p></div>
                                <div class="b-kv"><p class="b-label">Próximo cobro</p><p class="b-val-sm b-mono">{{ $billing['tenant']['next_charge_at'] }}</p></div>
                                <div class="b-kv"><p class="b-label">Gracia hasta</p><p class="b-val-sm b-mono">{{ $billing['tenant']['grace_until'] }}</p></div>
                                <div class="b-kv" style="grid-column:1/-1"><p class="b-label">Renovación</p><p class="b-val-sm">{{ $billing['tenant']['cancel_at_period_end'] ? 'Se detiene al final del periodo' : 'Activa automáticamente' }}</p></div>
                            </div>
                            <div class="b-kv" style="font-size:0.74rem;color:var(--muted);line-height:1.6">{{ $billing['tenant']['cancel_help'] }}</div>
                            @if($hasOpenInvoice && !$billing['open_invoice_module_id'])
                                <button type="button" @click="show('invoice')" class="b-pbar">
                                    <div><p style="font-size:0.79rem;font-weight:700;color:var(--amber)">Pago pendiente del plan</p><p style="font-size:0.71rem;opacity:.8;margin-top:0.1rem;color:var(--amber)">{{ $openInvoice['kind_label'] }} · {{ $openInvoice['total'] }}</p></div>
                                    <span class="b-badge b-badge-warning">Abrir →</span>
                                </button>
                            @endif
                            <hr class="b-hr">
                            <div style="display:flex;flex-wrap:wrap;gap:0.45rem">
                                @if($canManageCancellation)
                                    @if($billing['tenant']['cancel_at_period_end'])
                                        <form method="POST" action="{{ $billing['tenant']['resume_url'] }}">@csrf<button type="submit" class="b-btn b-btn-blue"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" stroke-linecap="round" stroke-linejoin="round"/></svg>Reactivar renovación</button></form>
                                    @else
                                        <form method="POST" action="{{ $billing['tenant']['cancel_url'] }}">@csrf<button type="submit" class="b-btn b-btn-amber"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14.25 9v6m-4.5 0V9M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke-linecap="round" stroke-linejoin="round"/></svg>Detener renovación</button></form>
                                    @endif
                                @endif
                                @if($hasOpenInvoice && $canPay)
                                    <button type="button" @click="show('invoice')" class="b-btn b-btn-ghost"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" stroke-linecap="round" stroke-linejoin="round"/></svg>Pagar ahora</button>
                                @endif
                            </div>
                            @if(!$canManageCancellation && !$canPay)
                                <div class="b-kv" style="font-size:0.73rem;color:var(--muted)">Solo un administrador puede cambiar la renovación o completar pagos.</div>
                            @endif
                        </section>

                        {{-- INVOICE --}}
                        @if($openInvoice)
                        <section x-cloak x-show="panel==='invoice'" x-transition.opacity.duration.150ms
                                 x-effect="if(panel==='invoice') renderPP()"
                                 style="display:flex;flex-direction:column;gap:0.7rem">
                            <div class="b-kv">
                                <p class="b-label">Total a pagar</p>
                                <p class="b-mono-big" style="margin-top:0.4rem">{{ $openInvoice['total'] }}</p>
                                <p style="font-size:0.71rem;color:var(--muted);margin-top:0.2rem">{{ $openInvoice['kind_label'] }}</p>
                            </div>
                            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:0.45rem">
                                <div class="b-kv"><p class="b-label">Vence</p><p class="b-val-sm b-mono">{{ $openInvoice['due_at'] }}</p></div>
                                <div class="b-kv"><p class="b-label">Estado</p><div style="margin-top:0.3rem"><span class="b-badge {{ $toneClasses[$openInvoice['status_tone']] ?? $toneClasses['neutral'] }}">{{ $openInvoice['status_label'] }}</span></div></div>
                                <div class="b-kv"><p class="b-label">Cubre</p><p class="b-val-sm" style="font-size:0.7rem">{{ $openInvoice['cover_range'] }}</p></div>
                            </div>
                            <div class="b-kv" style="font-size:0.74rem;color:var(--muted);line-height:1.6">{{ $openInvoice['help'] }}</div>
                            <div style="display:flex;flex-direction:column;gap:0.45rem">
                                @foreach($openInvoice['items'] as $item)
                                    <div class="b-iitem">
                                        <div><p style="font-size:0.82rem;font-weight:700;color:var(--ink)">{{ $item['description'] }}</p><p style="font-size:0.68rem;color:var(--muted);margin-top:0.1rem">{{ $item['kind_label'] }}</p><p style="font-size:0.66rem;color:var(--subtle);margin-top:0.06rem">{{ $item['period_range'] }}</p></div>
                                        <strong class="b-mono" style="font-size:0.86rem;color:var(--ink);white-space:nowrap;flex-shrink:0">{{ $item['amount'] }}</strong>
                                    </div>
                                @endforeach
                            </div>
                            <div id="bpm" class="hidden b-alert b-alert-danger" style="font-size:0.76rem"></div>
                            @if($billing['paypal']['client_id'] !== '' && $canPay)
                                <div class="b-kv"><p style="font-size:0.73rem;color:var(--muted);margin-bottom:0.7rem;line-height:1.55">Completa el pago con tu cuenta PayPal o con tarjeta dentro del flujo seguro.</p><div id="bpp"></div></div>
                            @elseif(!$canPay)
                                <div class="b-kv" style="font-size:0.73rem;color:var(--muted)">Un administrador debe completar este pago para continuar.</div>
                            @else
                                <div class="b-alert b-alert-danger" style="font-size:0.73rem">Falta configurar el cliente de PayPal.</div>
                            @endif
                        </section>
                        @endif

                        {{-- MODULES --}}
                        <?php foreach ($billing['modules'] as $module): ?>
                        <?php
                            $mNeedsPay = in_array($module['status'],['pending','past_due','grace'],true)||$module['is_current_open_invoice'];
                            $mBlocked  = $module['can_activate'] && $module['has_blocking_open_invoice'];
                        ?>
                        <section x-cloak x-show="isModule('{{ $module['id'] }}')" x-transition.opacity.duration.150ms style="display:flex;flex-direction:column;gap:0.7rem">
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem">
                                <div class="b-kv" style="grid-column:1/-1">
                                    <div style="display:flex;align-items:center;justify-content:space-between;gap:0.5rem;flex-wrap:wrap">
                                        <p style="font-size:0.875rem;font-weight:700;color:var(--ink)">{{ $module['name'] }}</p>
                                        <span class="b-badge {{ $toneClasses[$module['status_tone']] ?? $toneClasses['neutral'] }}">{{ $module['status_label'] }}</span>
                                    </div>
                                    <p style="font-size:0.73rem;color:var(--muted);margin-top:0.35rem;line-height:1.55">{{ $module['description'] }}</p>
                                </div>
                                <div class="b-kv"><p class="b-label">Plan</p><p class="b-val-sm">{{ $module['current_plan_label'] }}</p></div>
                                <div class="b-kv"><p class="b-label">Próximo cobro</p><p class="b-val-sm b-mono">{{ $module['next_charge_at'] }}</p></div>
                            </div>

                            @if($mBlocked)
                                <div class="b-alert b-alert-warning"><p style="font-size:0.79rem;font-weight:700">{{ $module['blocking_invoice_help'] }}</p><p style="font-size:0.71rem;opacity:.85;margin-top:0.2rem">Termina el pago pendiente antes para evitar cobros duplicados.</p></div>
                                <button type="button" @click="show('invoice')" class="b-btn b-btn-amber"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" stroke-linecap="round" stroke-linejoin="round"/></svg>Ver pago pendiente</button>
                            @elseif($mNeedsPay)
                                <div class="b-alert b-alert-warning"><p style="font-size:0.79rem;font-weight:700">Este módulo necesita regularización</p><p style="font-size:0.71rem;opacity:.85;margin-top:0.2rem">{{ $module['status_help'] }}</p></div>
                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem">
                                    <div class="b-kv"><p class="b-label">Estado</p><p class="b-val-sm">{{ $module['status_label'] }}</p></div>
                                    <div class="b-kv"><p class="b-label">Gracia hasta</p><p class="b-val-sm b-mono">{{ $module['grace_until'] }}</p></div>
                                </div>
                                @if($module['is_current_open_invoice'] && $hasOpenInvoice)
                                    <button type="button" @click="show('invoice')" class="b-btn b-btn-blue"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" stroke-linecap="round" stroke-linejoin="round"/></svg>Abrir pago pendiente de este módulo</button>
                                @elseif($hasOpenInvoice)
                                    <button type="button" @click="show('invoice')" class="b-btn b-btn-ghost"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" stroke-linecap="round" stroke-linejoin="round"/></svg>Ver pago pendiente actual</button>
                                @endif
                            @elseif($module['can_activate'])
                                @if(in_array($module['status'],['suspended','canceled'],true))
                                    <div class="b-kv" style="font-size:0.73rem;color:var(--muted)">Este módulo no está activo. Puedes volver a activarlo cuando lo necesites.</div>
                                @endif
                                <div>
                                    <p style="font-size:0.79rem;font-weight:700;color:var(--ink);margin-bottom:0.45rem">Elige tu ciclo</p>
                                    <div style="display:flex;flex-direction:column;gap:0.35rem">
                                        @foreach($module['activation_options'] as $opt)
                                            <button type="button" @click="setInt('{{ $module['id'] }}','{{ $opt['code'] }}')" :class="selInt('{{ $module['id'] }}')==='{{ $opt['code'] }}' ? 'b-opt on' : 'b-opt'">
                                                <div><p style="font-size:0.82rem;font-weight:700;color:var(--ink)">{{ $opt['label'] }}</p><p style="font-size:0.7rem;color:var(--muted);margin-top:0.08rem">{{ $opt['teaser'] }}</p></div>
                                                <span class="b-badge b-badge-neutral b-mono" style="flex-shrink:0">{{ $opt['full_amount'] }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                                @foreach($module['activation_options'] as $opt)
                                    <div x-cloak x-show="selInt('{{ $module['id'] }}')==='{{ $opt['code'] }}'" class="b-kv">
                                        <p style="font-size:0.76rem;font-weight:700;color:var(--ink);margin-bottom:0.5rem">Resumen de compra</p>
                                        @foreach([['Plan',$opt['label'],false],['Total hoy',$opt['today_amount'],true],['Precio ciclo',$opt['full_amount'],true],['Alineado hasta',$opt['cycle_ends_at'],false]] as [$l,$v,$m])
                                            <div style="display:flex;justify-content:space-between;gap:0.75rem;font-size:0.74rem;padding:0.18rem 0"><span style="color:var(--muted)">{{ $l }}</span><strong style="color:var(--ink)" class="{{ $m?'b-mono':'' }}">{{ $v }}</strong></div>
                                        @endforeach
                                        <p style="font-size:0.68rem;color:var(--subtle);margin-top:0.45rem;line-height:1.45">Hoy solo pagas la parte proporcional al tiempo restante.</p>
                                    </div>
                                @endforeach
                                @if($canManage)
                                    <form method="POST" action="{{ $module['subscribe_url'] }}">@csrf<input type="hidden" name="billing_interval" :value="selInt('{{ $module['id'] }}')"><button type="submit" class="b-btn b-btn-primary"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4.5v15m7.5-7.5h-15" stroke-linecap="round" stroke-linejoin="round"/></svg>Continuar con la activación</button></form>
                                @else
                                    <div class="b-kv" style="font-size:0.73rem;color:var(--muted)">Solo un administrador puede activar módulos.</div>
                                @endif
                            @else
                                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem">
                                    <div class="b-kv"><p class="b-label">Estado</p><p class="b-val-sm">{{ $module['status_label'] }}</p></div>
                                    <div class="b-kv"><p class="b-label">Gracia hasta</p><p class="b-val-sm b-mono">{{ $module['grace_until'] }}</p></div>
                                </div>
                                <div class="b-kv" style="font-size:0.74rem;color:var(--muted);line-height:1.6">{{ $module['status_help'] }}</div>
                                @if($module['cancel_at_period_end'])
                                    <div class="b-alert b-alert-warning" style="font-size:0.73rem">Este módulo seguirá hasta terminar el periodo actual, pero ya no se renovará.</div>
                                @endif
                                @if($canManageCancellation && $module['can_cancel'])
                                    <form method="POST" action="{{ $module['cancel_url'] }}">@csrf<button type="submit" class="b-btn b-btn-amber"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14.25 9v6m-4.5 0V9M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" stroke-linecap="round" stroke-linejoin="round"/></svg>Cancelar al final del periodo</button></form>
                                @elseif(!$canManageCancellation)
                                    <div class="b-kv" style="font-size:0.73rem;color:var(--muted)">Solo un administrador puede detener la renovación.</div>
                                @endif
                            @endif
                        </section>
                        <?php endforeach; ?>

                    </div>{{-- /b-panel-body --}}
                </div>{{-- /b-panel-frame --}}
            </div>{{-- /b-panel-col --}}

        </div>{{-- /b-stage --}}
    </div>{{-- /b --}}

    @if($openInvoice && $billing['paypal']['client_id'] !== '')
        <script src="https://www.paypal.com/sdk/js?client-id={{ urlencode($billing['paypal']['client_id']) }}&currency={{ urlencode($billing['paypal']['currency']) }}&intent=capture"></script>
    @endif

    <script>
    function billingWs(cfg) {
        return {
            open: cfg.initialPanel != null,
            panel: cfg.initialPanel ?? null,
            moduleId: null,
            ints: cfg.moduleIntervals ?? {},
            meta: cfg.moduleMeta ?? {},
            inv: cfg.invCfg ?? null,
            ppR: false, ppRing: false,

            init() {
                window.addEventListener('resize', () => this.lockScroll());
                if (this.panel === 'invoice') this.$nextTick(() => this.renderPP());
            },

            show(p) { this.panel = p; this.moduleId = null; this.open = true; if (p === 'invoice') this.$nextTick(() => this.renderPP()); },
            showMod(id) { this.panel = 'module'; this.moduleId = String(id); this.open = true; },
            close() { this.open = false; this.panel = null; this.moduleId = null; },
            isModule(id) { return this.panel === 'module' && this.moduleId === String(id); },

            selInt(id) { return this.ints[String(id)] ?? 'monthly'; },
            setInt(id, c) { this.ints[String(id)] = c; },

            eye() { const m = {'history':'Pagos y facturas','plan':'Plan base','invoice':'Factura pendiente','module':'Módulo'}; return m[this.panel] ?? ''; },
            ttl() { if (this.panel === 'history') return 'Historial'; if (this.panel === 'plan') return 'Plan principal'; if (this.panel === 'invoice') return 'Completar pago'; if (this.panel === 'module' && this.meta[this.moduleId]) return this.meta[this.moduleId].name; return ''; },
            sub() { if (this.panel === 'history') return 'Movimientos anteriores y periodo cubierto.'; if (this.panel === 'plan') return 'Estado de tu clínica y opciones de renovación.'; if (this.panel === 'invoice') return 'Completa esta factura para mantener tu servicio al día.'; if (this.panel === 'module' && this.meta[this.moduleId]) return 'Estado: ' + this.meta[this.moduleId].status_label; return ''; },

            lockScroll() { const l = this.open && window.innerWidth < 1024; document.documentElement.classList.toggle('overflow-hidden', l); document.body.classList.toggle('overflow-hidden', l); },

            showMsg(t) { if (!this.inv) return; const el = document.getElementById(this.inv.messageId); if (!el) return; el.textContent = t; el.classList.remove('hidden'); },

            renderPP() {
                if (!this.inv || this.ppR || this.ppRing) return;
                if (!window.paypal) { this.showMsg('No se pudo cargar PayPal.'); return; }
                const c = document.getElementById(this.inv.containerId);
                if (!c) return;
                this.ppRing = true;
                let btn;
                try {
                    btn = window.paypal.Buttons({
                        createOrder: () => fetch(this.inv.orderUrl, { method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN': @json(csrf_token())} })
                            .then(async r => { const p = await r.json(); if (!r.ok) throw new Error(p.message||'Error'); return p.orderId; }),
                        onApprove: d => fetch(this.inv.captureUrl, { method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN': @json(csrf_token())}, body: JSON.stringify({order_id: d.orderID}) })
                            .then(async r => { const p = await r.json(); if (!r.ok) throw new Error(p.message||'Error'); window.location.href = p.redirect_url; })
                            .catch(e => this.showMsg(e.message||'No se pudo confirmar el pago.')),
                        onCancel: () => this.showMsg('Cancelaste el pago. Puedes intentarlo nuevamente.'),
                        onError:  e  => this.showMsg(e.message||'Error con PayPal.'),
                    });
                } catch(e) { this.ppRing = false; this.showMsg(e.message||'Error al preparar PayPal.'); return; }
                Promise.resolve(btn.render('#' + this.inv.containerId))
                    .then(() => { this.ppR = true; })
                    .catch(e => { this.ppR = false; this.showMsg(e.message||'Error al mostrar PayPal.'); })
                    .finally(() => { this.ppRing = false; });
            },
        };
    }
    </script>
</x-filament-panels::page>
