<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تقرير الطالب — {{ $student->name }}</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        /* ── Page setup for printing ── */
        @page {
            size: A4 portrait;
            margin: 1.8cm 1.5cm 1.5cm 1.5cm;
        }

        body {
            font-family: 'Segoe UI', 'Tahoma', Arial, sans-serif;
            font-size: 12.5px;
            color: #1e293b;
            background: #f1f5f9;
            direction: rtl;
        }

        /* ── Screen wrapper ── */
        .page-wrap {
            max-width: 780px;
            margin: 24px auto;
            background: #fff;
            border-radius: 14px;
            padding: 32px 36px 28px;
            box-shadow: 0 4px 32px rgba(0,0,0,.10);
        }

        /* ── Header ── */
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid #1d4ed8;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }
        .report-logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .report-logo-icon {
            width: 44px; height: 44px;
            border-radius: 10px;
            background: #1d4ed8;
            color: #fff;
            font-size: 22px;
            font-weight: 900;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .report-title { font-size: 19px; font-weight: 900; color: #1d4ed8; line-height: 1.2; }
        .report-subtitle { font-size: 11.5px; color: #64748b; margin-top: 3px; }
        .report-meta { text-align: left; font-size: 11px; color: #94a3b8; line-height: 1.8; }
        .report-meta strong { color: #475569; }

        /* ── Info grid ── */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 18px;
        }
        .info-card {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 11px 13px;
            background: #f8fafc;
        }
        .info-card .label { font-size: 9.5px; color: #94a3b8; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; }
        .info-card .value { font-size: 20px; font-weight: 800; color: #0f172a; margin-top: 3px; }
        .info-card .sub   { font-size: 10.5px; color: #64748b; margin-top: 2px; }

        /* ── Progress section ── */
        .progress-section {
            display: flex;
            align-items: center;
            gap: 22px;
            margin-bottom: 18px;
            padding: 14px 18px;
            border: 1px solid #bfdbfe;
            border-radius: 12px;
            background: linear-gradient(135deg, #eff6ff 0%, #f0fdf4 100%);
            page-break-inside: avoid;
        }
        .ring-wrap { position: relative; width: 90px; height: 90px; flex-shrink: 0; }
        .ring-wrap svg { transform: rotate(-90deg); }
        .ring-label {
            position: absolute; inset: 0;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            font-size: 17px; font-weight: 900; color: #1d4ed8;
        }
        .ring-label small { font-size: 8.5px; color: #64748b; font-weight: 600; margin-top: -2px; }
        .progress-details h3 { font-size: 14px; font-weight: 700; margin-bottom: 7px; color: #0f172a; }
        .progress-details p  { font-size: 11.5px; color: #475569; margin-bottom: 3px; }
        .progress-details b  { color: #0f172a; }

        /* ── Section title ── */
        .section-title {
            font-size: 12px; font-weight: 700;
            color: #1d4ed8;
            border-right: 4px solid #1d4ed8;
            padding-right: 8px;
            margin-bottom: 9px;
            margin-top: 18px;
        }

        /* ── Tables ── */
        table { width: 100%; border-collapse: collapse; font-size: 11.5px; page-break-inside: auto; }
        thead { display: table-header-group; }
        thead th {
            background: #1d4ed8; color: #fff;
            padding: 7px 9px; text-align: right;
            font-weight: 600; font-size: 11px;
        }
        thead th:first-child { border-radius: 0 6px 6px 0; }
        thead th:last-child  { border-radius: 6px 0 0 6px; }
        tbody tr { page-break-inside: avoid; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody td { padding: 6px 9px; border-bottom: 1px solid #f1f5f9; }

        .badge {
            display: inline-block; padding: 2px 8px; border-radius: 20px;
            font-size: 10px; font-weight: 700;
        }
        .badge-new       { background: #dcfce7; color: #16a34a; }
        .badge-review    { background: #dbeafe; color: #1d4ed8; }
        .badge-excellent { background: #fef9c3; color: #ca8a04; }
        .badge-good      { background: #e0f2fe; color: #0369a1; }
        .badge-weak      { background: #fee2e2; color: #dc2626; }

        /* ── Progress bar ── */
        .bar-wrap { background: #e2e8f0; border-radius: 4px; height: 6px; min-width: 55px; }
        .bar-fill  { background: #10b981; height: 6px; border-radius: 4px; }

        /* ── Footer ── */
        .report-footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            font-size: 10px;
            color: #94a3b8;
            display: flex;
            justify-content: space-between;
        }

        /* ── Print action bar (screen only) ── */
        .print-bar {
            max-width: 780px;
            margin: 20px auto 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 4px;
        }
        .print-btn {
            background: #1d4ed8; color: #fff;
            border: none; border-radius: 9px;
            padding: 10px 22px; font-size: 13px;
            font-weight: 700; cursor: pointer;
            display: flex; align-items: center; gap: 7px;
            box-shadow: 0 3px 10px rgba(29,78,216,.30);
            transition: background .2s;
            text-decoration: none;
        }
        .print-btn:hover { background: #1e40af; }
        .back-btn {
            color: #64748b; font-size: 12px; font-weight: 500;
            text-decoration: none; display: flex; align-items: center; gap: 5px;
        }
        .back-btn:hover { color: #0f172a; }

        /* ── Print overrides ── */
        @media print {
            /* Force all background colors/images to print */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            body { background: #fff !important; }

            .print-bar { display: none !important; }

            .page-wrap {
                max-width: 100%;
                margin: 0;
                border-radius: 0;
                padding: 0;
                box-shadow: none;
            }

            /* Prevent orphaned headings */
            .section-title { page-break-after: avoid; }

            /* Keep progress ring + cards together */
            .progress-section,
            .info-grid { page-break-inside: avoid; }

            /* Table rows don't break mid-row */
            tr { page-break-inside: avoid; }

            a { color: inherit !important; text-decoration: none !important; }
        }

        @media (max-width: 640px) {
            .page-wrap { margin: 0; border-radius: 0; padding: 18px 16px 20px; box-shadow: none; }
            .info-grid { grid-template-columns: 1fr 1fr; }
            .report-header { flex-direction: column; gap: 10px; }
            .report-meta { text-align: right; }
            .progress-section { flex-direction: column; align-items: flex-start; gap: 14px; }
            .print-bar { padding: 12px 16px; flex-direction: column; gap: 10px; align-items: stretch; }
            .print-btn { justify-content: center; }
        }
    </style>
</head>
<body>

{{-- Action bar (screen only) --}}
<div class="print-bar">
    <a href="javascript:history.back()" class="back-btn">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        العودة
    </a>
    <button class="print-btn" onclick="window.print()">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
        </svg>
        طباعة / حفظ PDF
    </button>
</div>

{{-- Report card --}}
<div class="page-wrap">

    {{-- Header --}}
    <div class="report-header">
        <div class="report-logo">
            <div class="report-logo-icon">ق</div>
            <div>
                <div class="report-title">تقرير حفظ القرآن الكريم</div>
                <div class="report-subtitle">
                    {{ $student->halaqa?->name ?? '—' }}{{ $student->halaqa?->center ? ' · ' . $student->halaqa->center->name : '' }}
                </div>
            </div>
        </div>
        <div class="report-meta">
            <div><strong>تاريخ التقرير:</strong> {{ now()->format('Y/m/d') }}</div>
            <div><strong>اسم الطالب:</strong> {{ $student->name }}</div>
            @if($student->age)
            <div><strong>العمر:</strong> {{ $student->age }} سنة</div>
            @endif
        </div>
    </div>

    {{-- Info cards --}}
    <div class="info-grid">
        <div class="info-card">
            <div class="label">إجمالي الآيات المحفوظة</div>
            <div class="value">{{ number_format($totalNewAyahs) }}</div>
            <div class="sub">من أصل 6,236 آية</div>
        </div>
        <div class="info-card">
            <div class="label">آيات المراجعة</div>
            <div class="value">{{ number_format($totalReviewAyahs) }}</div>
            <div class="sub">مجموع جلسات المراجعة</div>
        </div>
        <div class="info-card">
            <div class="label">عدد الجلسات</div>
            <div class="value">{{ $sessionsCount }}</div>
            <div class="sub">آخر جلسة: {{ $lastSessionDate ?? '—' }}</div>
        </div>
    </div>

    {{-- Progress ring --}}
    @php $circumf = round(2 * pi() * 38, 2); $offset = round($circumf * (1 - $progressPct / 100), 2); @endphp
    <div class="progress-section">
        <div class="ring-wrap">
            <svg viewBox="0 0 100 100" width="90" height="90">
                <circle cx="50" cy="50" r="38" fill="none" stroke="#e2e8f0" stroke-width="10"/>
                <circle cx="50" cy="50" r="38" fill="none" stroke="#10b981" stroke-width="10"
                        stroke-linecap="round"
                        stroke-dasharray="{{ $circumf }}"
                        stroke-dashoffset="{{ $offset }}"/>
            </svg>
            <div class="ring-label">
                {{ $progressPct }}%
                <small>من القرآن</small>
            </div>
        </div>
        <div class="progress-details">
            <h3>تقدم الحفظ</h3>
            <p>حفظ الطالب <b>{{ number_format($totalNewAyahs) }} آية</b> من إجمالي 6,236 آية</p>
            <p>ما يعادل <b>{{ round($totalNewAyahs / 20, 1) }} صفحة</b> من المصحف (تقريباً)</p>
            <p>وما يعادل <b>{{ round($totalNewAyahs / 604, 2) }} جزء</b> من 30 جزءاً</p>
            @if($surahsCount > 0)
                <p>موزّعة على <b>{{ $surahsCount }} سورة</b></p>
            @endif
        </div>
    </div>

    {{-- Surahs breakdown --}}
    @if($surahStats->isNotEmpty())
        <div class="section-title">تفاصيل الحفظ حسب السورة</div>
        <table>
            <thead>
                <tr>
                    <th style="width:40px">#</th>
                    <th>السورة</th>
                    <th style="width:90px">محفوظة</th>
                    <th style="width:90px">إجمالي آيات</th>
                    <th style="width:130px">التقدم</th>
                </tr>
            </thead>
            <tbody>
                @foreach($surahStats as $s)
                @php $pct = min(100, round($s->ayahs / $s->ayahs_count * 100)); @endphp
                <tr>
                    <td style="color:#94a3b8;font-size:11px">{{ $s->number }}</td>
                    <td><strong>{{ $s->name }}</strong></td>
                    <td>{{ $s->ayahs }}</td>
                    <td style="color:#94a3b8">{{ $s->ayahs_count }}</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:6px;">
                            <div class="bar-wrap" style="flex:1">
                                <div class="bar-fill" style="width:{{ $pct }}%"></div>
                            </div>
                            <span style="font-size:10px;color:#475569;white-space:nowrap;min-width:30px">{{ $pct }}%</span>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Last sessions --}}
    @if($sessions->isNotEmpty())
        <div class="section-title">آخر {{ $sessions->count() }} جلسة تسميع</div>
        <table>
            <thead>
                <tr>
                    <th style="width:80px">التاريخ</th>
                    <th style="width:60px">النوع</th>
                    <th>السورة</th>
                    <th style="width:70px">النطاق</th>
                    <th style="width:70px">التقييم</th>
                    <th>المحفّظ</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $ratingLabels = [
                        'excellent' => 'ممتاز', 'very_good' => 'جيد جداً',
                        'good' => 'جيد', 'weak' => 'ضعيف', 'repeat' => 'يعاد',
                    ];
                    $ratingClasses = [
                        'excellent' => 'badge-excellent', 'very_good' => 'badge-good',
                        'good' => 'badge-good', 'weak' => 'badge-weak', 'repeat' => 'badge-weak',
                    ];
                @endphp
                @foreach($sessions as $s)
                <tr>
                    <td style="color:#64748b">{{ optional($s->heard_at)->format('Y/m/d') }}</td>
                    <td>
                        <span class="badge {{ $s->type === 'new' ? 'badge-new' : 'badge-review' }}">
                            {{ $s->type === 'new' ? 'جديد' : 'مراجعة' }}
                        </span>
                    </td>
                    <td>{{ $s->surah?->name ?? '—' }}</td>
                    <td dir="ltr" style="font-size:11px;color:#475569">{{ $s->from_ayah }}–{{ $s->to_ayah }}</td>
                    <td>
                        <span class="badge {{ $ratingClasses[$s->rating] ?? '' }}">
                            {{ $ratingLabels[$s->rating] ?? $s->rating }}
                        </span>
                    </td>
                    <td style="color:#475569">{{ $s->muhafidh?->name ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Footer --}}
    <div class="report-footer">
        <span>نظام مركز تحفيظ القرآن الكريم</span>
        <span>تم إنشاء التقرير: {{ now()->format('Y/m/d H:i') }}</span>
    </div>

</div>{{-- /page-wrap --}}

</body>
</html>
