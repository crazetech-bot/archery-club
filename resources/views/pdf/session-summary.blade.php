<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Session Summary — {{ $archer->name }}</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
      font-size: 11px;
      color: #111827;
      background: #fff;
      padding: 32px 40px;
    }

    /* ── Header ── */
    .header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      border-bottom: 2px solid #111827;
      padding-bottom: 16px;
      margin-bottom: 24px;
    }
    .header-title {
      font-size: 20px;
      font-weight: 700;
      letter-spacing: -0.3px;
    }
    .header-sub {
      font-size: 11px;
      color: #6b7280;
      margin-top: 2px;
    }
    .header-meta {
      text-align: right;
      color: #6b7280;
      font-size: 10px;
      line-height: 1.6;
    }

    /* ── Stats row ── */
    .stats-row {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 12px;
      margin-bottom: 24px;
    }
    .stat-card {
      border: 1px solid #e5e7eb;
      border-radius: 8px;
      padding: 12px;
    }
    .stat-label {
      font-size: 9px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      color: #9ca3af;
    }
    .stat-value {
      font-size: 22px;
      font-weight: 700;
      color: #111827;
      margin-top: 2px;
    }
    .stat-unit {
      font-size: 10px;
      color: #9ca3af;
      margin-top: 1px;
    }

    /* ── Section titles ── */
    .section-title {
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.06em;
      color: #6b7280;
      margin-bottom: 8px;
    }

    /* ── Ends table ── */
    .ends-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 24px;
    }
    .ends-table th {
      background: #f9fafb;
      border: 1px solid #e5e7eb;
      padding: 6px 8px;
      font-size: 9px;
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: #6b7280;
      text-align: left;
    }
    .ends-table td {
      border: 1px solid #f3f4f6;
      padding: 6px 8px;
      vertical-align: middle;
    }
    .ends-table tr:nth-child(even) td {
      background: #fafafa;
    }

    /* Arrow score badges */
    .arrows-row { display: flex; gap: 4px; flex-wrap: wrap; }
    .arrow-badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 22px;
      height: 22px;
      border-radius: 50%;
      font-size: 9px;
      font-weight: 700;
    }
    .arrow-x   { background: #fbbf24; color: #111827; }
    .arrow-10  { background: #fde68a; color: #111827; }
    .arrow-8-9 { background: #ef4444; color: white; }
    .arrow-6-7 { background: #f87171; color: white; }
    .arrow-4-5 { background: #38bdf8; color: white; }
    .arrow-2-3 { background: #7dd3fc; color: white; }
    .arrow-1   { background: #e5e7eb; color: #374151; }
    .arrow-m   { background: white;   color: #9ca3af; border: 1px solid #e5e7eb; }

    /* ── Session info ── */
    .info-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 8px 24px;
      margin-bottom: 24px;
    }
    .info-row {
      display: flex;
      justify-content: space-between;
      border-bottom: 1px solid #f3f4f6;
      padding: 5px 0;
    }
    .info-label { color: #9ca3af; }
    .info-value { font-weight: 500; }

    /* ── Footer ── */
    .footer {
      border-top: 1px solid #e5e7eb;
      padding-top: 12px;
      margin-top: 32px;
      font-size: 9px;
      color: #9ca3af;
      display: flex;
      justify-content: space-between;
    }
  </style>
</head>
<body>

  <!-- Header -->
  <div class="header">
    <div>
      <div class="header-title">Training Session Summary</div>
      <div class="header-sub">{{ $session->round_type ?? 'Practice Session' }}</div>
    </div>
    <div class="header-meta">
      <div>{{ $archer->name }}</div>
      <div>{{ $archer->category }}</div>
      <div>{{ \Carbon\Carbon::parse($session->started_at)->format('j M Y') }}</div>
    </div>
  </div>

  <!-- Stats row -->
  <div class="stats-row">
    <div class="stat-card">
      <div class="stat-label">Total Score</div>
      <div class="stat-value">{{ $liveSession->total_score ?? '—' }}</div>
      @if($session->max_score)
        <div class="stat-unit">/ {{ $session->max_score }}</div>
      @endif
    </div>
    <div class="stat-card">
      <div class="stat-label">Avg / End</div>
      <div class="stat-value">{{ $liveSession?->average_per_end ? number_format($liveSession->average_per_end, 1) : '—' }}</div>
      <div class="stat-unit">points</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">X Count</div>
      <div class="stat-value">{{ $liveSession->x_count ?? '—' }}</div>
      <div class="stat-unit">perfect</div>
    </div>
    <div class="stat-card">
      <div class="stat-label">Duration</div>
      <div class="stat-value">
        @php
          $dur = $session->ended_at
            ? \Carbon\Carbon::parse($session->started_at)->diffInMinutes($session->ended_at)
            : null;
          echo $dur ? ($dur < 60 ? "{$dur}m" : floor($dur/60).'h '.($dur%60).'m') : '—';
        @endphp
      </div>
      <div class="stat-unit">total time</div>
    </div>
  </div>

  <!-- Session info -->
  <div class="section-title">Session Details</div>
  <div class="info-grid">
    <div>
      <div class="info-row">
        <span class="info-label">Round Type</span>
        <span class="info-value">{{ $session->round_type ?? 'Practice' }}</span>
      </div>
      <div class="info-row">
        <span class="info-label">Distance</span>
        <span class="info-value">{{ $session->distance_metres ? $session->distance_metres.'m' : '—' }}</span>
      </div>
      <div class="info-row">
        <span class="info-label">Arrows per End</span>
        <span class="info-value">{{ $liveSession->arrows_per_end ?? '—' }}</span>
      </div>
    </div>
    <div>
      <div class="info-row">
        <span class="info-label">Started</span>
        <span class="info-value">{{ \Carbon\Carbon::parse($session->started_at)->format('H:i') }}</span>
      </div>
      <div class="info-row">
        <span class="info-label">Finished</span>
        <span class="info-value">{{ $session->ended_at ? \Carbon\Carbon::parse($session->ended_at)->format('H:i') : 'Ongoing' }}</span>
      </div>
      <div class="info-row">
        <span class="info-label">Coach</span>
        <span class="info-value">{{ $session->coach?->name ?? '—' }}</span>
      </div>
    </div>
  </div>

  <!-- Ends breakdown -->
  <div class="section-title">End-by-End Breakdown</div>
  <table class="ends-table">
    <thead>
      <tr>
        <th style="width: 48px">End</th>
        <th>Arrows</th>
        <th style="width: 56px; text-align: right">Total</th>
        <th style="width: 48px; text-align: right">X</th>
        <th style="width: 48px; text-align: right">10+X</th>
      </tr>
    </thead>
    <tbody>
      @foreach($ends as $end)
        <tr>
          <td style="font-weight: 600">{{ $end->end_number }}</td>
          <td>
            <div class="arrows-row">
              @foreach($end->arrows as $arrow)
                @php
                  $score = $arrow->score;
                  $n = is_numeric($score) ? (int)$score : ($score === 'X' ? 10 : 0);
                  $cls = match(true) {
                    $score === 'X' => 'arrow-x',
                    $score === '10' => 'arrow-10',
                    $n >= 8 => 'arrow-8-9',
                    $n >= 6 => 'arrow-6-7',
                    $n >= 4 => 'arrow-4-5',
                    $n >= 2 => 'arrow-2-3',
                    $n === 1 => 'arrow-1',
                    default => 'arrow-m',
                  };
                @endphp
                <span class="arrow-badge {{ $cls }}">{{ $score }}</span>
              @endforeach
            </div>
          </td>
          <td style="text-align: right; font-weight: 700">{{ $end->total_score }}</td>
          <td style="text-align: right; color: #d97706">{{ $end->x_count ?: '—' }}</td>
          <td style="text-align: right; color: #6b7280">{{ ($end->x_count + $end->ten_count) ?: '—' }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <!-- Footer -->
  <div class="footer">
    <span>Generated by Archery Performance OS</span>
    <span>{{ now()->format('j M Y, H:i') }}</span>
  </div>

</body>
</html>
