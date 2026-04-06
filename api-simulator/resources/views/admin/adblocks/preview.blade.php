<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdBlock Preview #{{ $zone->id }}</title>
    <style>
        body {
            margin: 0;
            font-family: system-ui, sans-serif;
            background: #f1f5f9;
            color: #0f172a;
        }

        .page {
            max-width: 1100px;
            margin: 0 auto;
            padding: 32px 16px;
        }

        .card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 24px;
            box-shadow: 0 1px 2px rgba(15, 23, 42, 0.06);
        }

        .header {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
            align-items: center;
            margin-bottom: 24px;
        }

        .eyebrow {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #94a3b8;
        }

        .title {
            margin: 6px 0 0;
            font-size: 30px;
            font-weight: 700;
        }

        .muted {
            color: #64748b;
            font-size: 14px;
        }

        .actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            padding: 10px 16px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #475569;
        }

        .btn-dark {
            background: #0f172a;
            border-color: #0f172a;
            color: #fff;
        }

        .grid {
            display: grid;
            gap: 24px;
            grid-template-columns: minmax(0, 1.3fr) minmax(280px, 0.7fr);
        }

        .preview-shell {
            border: 1px dashed #cbd5e1;
            background: #f8fafc;
            border-radius: 16px;
            padding: 24px;
        }

        .pill {
            display: inline-flex;
            border-radius: 999px;
            background: #ecfdf5;
            color: #047857;
            font-size: 12px;
            font-weight: 700;
            padding: 6px 12px;
        }

        pre {
            margin: 16px 0 0;
            overflow-x: auto;
            border-radius: 14px;
            padding: 16px;
            background: #020617;
            color: #e2e8f0;
            font-size: 12px;
            line-height: 1.6;
        }

        dl {
            margin: 16px 0 0;
        }

        .row {
            display: flex;
            justify-content: space-between;
            gap: 16px;
            padding: 10px 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 14px;
        }

        .row:last-child {
            border-bottom: 0;
        }

        .row dt {
            color: #64748b;
        }

        .row dd {
            margin: 0;
            font-weight: 600;
            text-align: right;
            word-break: break-word;
        }

        .test-grid {
            display: grid;
            gap: 12px;
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            margin-top: 16px;
        }

        .field label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #475569;
            margin-bottom: 6px;
        }

        .field input,
        .field select {
            width: 100%;
            box-sizing: border-box;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            padding: 10px 12px;
            font-size: 14px;
            background: #fff;
            color: #0f172a;
        }

        .helper {
            margin-top: 8px;
            font-size: 12px;
            color: #64748b;
        }

        @media (max-width: 900px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="card" style="margin-bottom: 24px;">
            <div class="header">
                <div>
                    <p class="eyebrow">Test Page</p>
                    <h1 class="title">AdBlock Preview</h1>
                    <p class="muted" style="margin-top: 10px;">
                        Zone #{{ $zone->id }} · {{ $zone->name }} · {{ $zone->format_key ?: 'unknown format' }} · {{ $zone->size_key ?: 'auto' }}
                    </p>
                </div>
                <div class="actions">
                    <a href="{{ route('admin.adblocks') }}" class="btn">
                        Back to AdBlocks
                    </a>
                    <button type="button" onclick="location.reload()" class="btn btn-dark">
                        Reload Preview
                    </button>
                </div>
            </div>
        </div>

        <div class="card" style="margin-bottom: 24px;">
            <div class="header" style="margin-bottom: 0;">
                <div>
                    <h2 style="margin: 0; font-size: 18px;">Targeting Test Controls</h2>
                    <p class="muted" style="margin: 8px 0 0;">Use these values to simulate visitor targeting on the real zone script.</p>
                </div>
            </div>
            <form method="GET" class="test-grid">
                <div class="field">
                    <label for="device">Device</label>
                    <select id="device" name="device">
                        <option value="">Auto detect</option>
                        <option value="desktop" {{ ($previewParams['device'] ?? '') === 'desktop' ? 'selected' : '' }}>Desktop</option>
                        <option value="mobile" {{ ($previewParams['device'] ?? '') === 'mobile' ? 'selected' : '' }}>Mobile</option>
                        <option value="tablet" {{ ($previewParams['device'] ?? '') === 'tablet' ? 'selected' : '' }}>Tablet</option>
                    </select>
                </div>
                <div class="field">
                    <label for="country">Country</label>
                    <input id="country" name="country" maxlength="2" value="{{ $previewParams['country'] ?? '' }}" placeholder="AL">
                </div>
                <div class="field">
                    <label for="age">Age</label>
                    <input id="age" name="age" type="number" min="0" value="{{ $previewParams['age'] ?? '' }}" placeholder="25">
                </div>
                <div class="field">
                    <label for="gender">Gender</label>
                    <select id="gender" name="gender">
                        <option value="">Any</option>
                        <option value="male" {{ ($previewParams['gender'] ?? '') === 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ ($previewParams['gender'] ?? '') === 'female' ? 'selected' : '' }}>Female</option>
                    </select>
                </div>
                <div class="field">
                    <label for="color">Color</label>
                    <input id="color" name="color" value="{{ $previewParams['color'] ?? '' }}" placeholder="blue">
                </div>
                <div class="field">
                    <label for="height">Height</label>
                    <input id="height" name="height" type="number" min="0" value="{{ $previewParams['height'] ?? '' }}" placeholder="175">
                </div>
                <div class="field">
                    <label for="weight">Weight</label>
                    <input id="weight" name="weight" type="number" min="0" value="{{ $previewParams['weight'] ?? '' }}" placeholder="70">
                </div>
                <div class="field" style="display:flex;align-items:end;gap:8px;">
                    <button type="submit" class="btn btn-dark" style="width:100%;">Apply Test</button>
                </div>
            </form>
            <p class="helper">If the zone matches the targeting rules, the ad renders below. If it fails, the Rendered Zone area stays blank.</p>
        </div>

        <div class="grid">
            <div class="card">
                <div class="header" style="margin-bottom: 16px;">
                    <div>
                        <h2 style="margin: 0; font-size: 18px;">Rendered Zone</h2>
                        <p class="muted" style="margin: 8px 0 0;">This page includes the real zone container and the served JavaScript.</p>
                    </div>
                    <span class="pill">Live Test</span>
                </div>

                <div class="preview-shell">
                    {!! $adCode !!}
                </div>
            </div>

            <div style="display: grid; gap: 24px;">
                <div class="card">
                    <h2 style="margin: 0; font-size: 18px;">Embed Snippet</h2>
                    <p class="muted" style="margin-top: 8px;">Use this exact structure on a test page.</p>
                    <pre><code>{{ e($adCode) }}</code></pre>
                </div>

                <div class="card">
                    <h2 style="margin: 0; font-size: 18px;">Zone Details</h2>
                    <dl>
                        <div class="row">
                            <dt>Site</dt>
                            <dd>{{ $zone->site->name ?? 'Unknown' }}</dd>
                        </div>
                        <div class="row">
                            <dt>Placement</dt>
                            <dd>{{ $zone->placement }}</dd>
                        </div>
                        <div class="row">
                            <dt>Format</dt>
                            <dd>{{ $zone->format_key ?: 'Unknown' }}</dd>
                        </div>
                        <div class="row">
                            <dt>Size</dt>
                            <dd>{{ $zone->size_key ?: 'Auto' }}</dd>
                        </div>
                        <div class="row">
                            <dt>Serve URL</dt>
                            <dd>{{ $serveUrl }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
