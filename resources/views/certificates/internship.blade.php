<!DOCTYPE html>
<html>

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <title>Sertifikat Magang - {{ $evaluation->intern->name }}</title>
  <style>
    @page {
      size: A4 landscape;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: DejaVu Sans, sans-serif;
      color: #1e293b;
    }

    .page {
      position: relative;
      width: 297mm;
      page-break-after: always;
    }

    .page:last-child {
      page-break-after: avoid;
    }

    /* Page 1 Styles */
    .certificate-border {
      border: 4px solid #1d4ed8;
      padding: 20px;
      min-height: 197.2mm;
      position: relative;
    }

    .inner-border {
      border: 1px solid #60a5fa;
      border-radius: 6px;
      padding: 25px;
      text-align: center;
    }

    .header {
      margin-bottom: 20px;
    }

    .company-logo {
      font-size: 32px;
      font-weight: 900;
      color: #1d4ed8;
      letter-spacing: 4px;
      margin-bottom: 5px;
    }

    .company-tagline {
      font-size: 11px;
      color: #64748b;
      letter-spacing: 2px;
    }

    .cert-title {
      font-size: 40px;
      font-weight: 300;
      letter-spacing: 8px;
      color: #1e293b;
      margin: 30px 0 10px;
    }

    .cert-subtitle {
      font-size: 14px;
      color: #64748b;
      letter-spacing: 2px;
      margin-bottom: 25px;
    }

    .intro {
      font-size: 14px;
      color: #475569;
      margin: 0;
    }

    .recipient {
      font-size: 38px;
      font-weight: 700;
      color: #1d4ed8;
      margin: 5px 0 0 0;
      border-bottom: 3px solid #1d4ed8;
      display: inline-block;
      padding: 5px 60px 10px;
    }

    .position-info {
      font-size: 16px;
      color: #475569;
      margin: 15px 0;
    }

    .period-info {
      font-size: 13px;
      color: #64748b;
    }

    .grade-section {
      margin-top: 15px;
    }

    .grade-title {
      font-size: 11px;
      color: #64748b;
      letter-spacing: 1px;
    }

    .grade-badge {
      background-color: #1d4ed8;
      color: white;
      padding: 12px 60px;
      border-radius: 8px;
      font-size: 24px;
      font-weight: 700;
      display: inline-block;
      margin: 10px 0;
    }

    .grade-value {
      font-size: 13px;
      color: #64748b;
    }

    .signatures {
      margin-top: 30px;
      width: 100%;
    }

    .signatures table {
      width: 100%;
      border-collapse: collapse;
    }

    .signatures td {
      width: 50%;
      text-align: center;
      padding-top: 60px;
    }

    .sign-line {
      width: 180px;
      border-bottom: 1px solid #94a3b8;
      margin: 0 auto 10px;
    }

    .sign-name {
      font-size: 14px;
      font-weight: 700;
      color: #1e293b;
      margin-bottom: 3px;
    }

    .sign-role {
      font-size: 11px;
      color: #64748b;
    }

    .cert-footer {
      position: absolute;
      bottom: 15px;
      left: 0;
      right: 0;
      text-align: center;
      font-size: 9px;
      color: #94a3b8;
    }

    .details-page {
      border: 4px solid #1d4ed8;
      padding: 20px;
      min-height: 197.2mm;
    }

    .score-table {
      page-break-inside: auto;
    }

    .score-table thead {
      display: table-header-group;
    }

    .score-table tbody tr {
      page-break-inside: avoid;
    }

    .legend,
    .footer-note {
      page-break-before: avoid;
    }

    .details-header {
      text-align: center;
      border-bottom: 2px solid #e2e8f0;
      padding-bottom: 15px;
      margin-bottom: 20px;
    }

    .details-title {
      font-size: 20px;
      font-weight: 800;
      color: #1d4ed8;
      margin-bottom: 5px;
    }

    .details-subtitle {
      font-size: 12px;
      color: #64748b;
    }

    .info-box {
      background-color: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      padding: 15px;
      margin-bottom: 20px;
    }

    .info-table {
      width: 100%;
    }

    .info-table td {
      padding: 6px 10px;
      font-size: 11px;
    }

    .info-table .label {
      color: #64748b;
      width: 100px;
    }

    .info-table .value {
      color: #1e293b;
      font-weight: 700;
    }

    .score-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }

    .score-table th {
      background-color: #1d4ed8;
      color: white;
      padding: 10px;
      text-align: left;
      font-size: 11px;
      font-weight: 700;
    }

    .score-table th:last-child {
      text-align: center;
      width: 80px;
    }

    .score-table td {
      padding: 8px 10px;
      border-bottom: 1px solid #e2e8f0;
      font-size: 12px;
      color: #334155;
    }

    .score-table td:last-child {
      text-align: center;
      font-weight: 700;
      color: #1d4ed8;
    }

    .section-header td {
      background-color: #f1f5f9;
      color: #475569;
      font-weight: 700;
      font-size: 10px;
      text-transform: uppercase;
      padding: 8px 10px;
    }

    .total-row td {
      background-color: #1d4ed8;
      color: white !important;
      font-weight: 700;
      font-size: 14px;
      padding: 12px 10px;
    }

    .legend {
      text-align: center;
      margin-top: 20px;
      font-size: 10px;
    }

    .legend-item {
      display: inline-block;
      margin: 0 10px;
      padding: 4px 12px;
      border-radius: 4px;
      font-weight: 600;
    }

    .legend-exc {
      background-color: #dcfce7;
      color: #166534;
    }

    .legend-good {
      background-color: #dbeafe;
      color: #1e40af;
    }

    .legend-fair {
      background-color: #fef3c7;
      color: #92400e;
    }

    .legend-poor {
      background-color: #fee2e2;
      color: #991b1b;
    }

    .footer-note {
      text-align: center;
      font-size: 9px;
      color: #94a3b8;
      margin-top: 20px;
      line-height: 1.6;
    }
  </style>
</head>

<body>

  <!-- PAGE 1: CERTIFICATE -->
  <div class="page">
    <div class="certificate-border">
      <div class="inner-border">
        <div class="header">
          <div class="company-logo">INOSOFT</div>
          <div class="company-tagline">Innovation Software Technology</div>
        </div>

        <div class="cert-title">SERTIFIKAT</div>
        <div class="cert-subtitle">PENYELESAIAN PROGRAM MAGANG</div>

        <div class="intro">Dengan ini menyatakan bahwa:</div>

        <div class="recipient">{{ $evaluation->intern->name }}</div>

        <div class="position-info">
          Telah menyelesaikan program magang sebagai<br>
          <strong>{{ $internship->custom_position ?? $internship->job?->title ?? 'Intern' }}</strong>
        </div>

        <div class="period-info">
          Periode: {{ $internship->start_date->format('d F Y') }} - {{ $internship->end_date->format('d F Y') }}
        </div>

        <div class="grade-section">
          <div class="grade-title">PREDIKAT</div>
          <div class="grade-badge">{{ $evaluation->getGradeLabel() }}</div>
          <div class="grade-value">Nilai Akhir: {{ $evaluation->overall_rating }}/100</div>
        </div>

        <div class="signatures">
          <table>
            <tr>
              <td>
                <div class="sign-line"></div>
                <div class="sign-name">{{ $evaluation->mentor->name }}</div>
                <div class="sign-role">Mentor</div>
              </td>
              <td>
                <div class="sign-line"></div>
                <div class="sign-name">HR Manager</div>
                <div class="sign-role">PT Inosoft Trans Sistem</div>
              </td>
            </tr>
          </table>
        </div>
      </div>

      <div class="cert-footer">
        <strong>No. Sertifikat:</strong> CERT/{{ strtoupper(substr($evaluation->id, 0, 8)) }}/{{ now()->format('Y') }} |
        <strong>Diterbitkan:</strong> {{ now()->format('d F Y') }}
      </div>
    </div>
  </div>

  @php
  $maxRowsPage2 = 11; 
  $maxRowsContinuation = 12; 

  $standardCriteria = [
  ['name' => 'Kemampuan Teknis', 'rating' => $evaluation->technical_skill],
  ['name' => 'Komunikasi', 'rating' => $evaluation->communication],
  ['name' => 'Kerja Tim', 'rating' => $evaluation->teamwork],
  ['name' => 'Inisiatif', 'rating' => $evaluation->initiative],
  ['name' => 'Kedisiplinan', 'rating' => $evaluation->punctuality],
  ];

  $customCriteria = $evaluation->custom_criteria ?? [];
  $customCriteriaCount = count($customCriteria);

  $availableOnPage2 = max(0, $maxRowsPage2 - 6 - ($customCriteriaCount > 0 ? 1 : 0)); // 6 for standard + header

  $customPage2 = array_slice($customCriteria, 0, $availableOnPage2);
  $customOverflow = array_slice($customCriteria, $availableOnPage2);

  $overflowChunks = $customOverflow ? array_chunk($customOverflow, $maxRowsContinuation) : [];
  @endphp

  <!-- Page 2: Main Assessment -->
  <div class="page">
    <div class="details-page">
      <div class="details-header">
        <div class="details-title">LAMPIRAN PENILAIAN</div>
        <div class="details-subtitle">Detail Hasil Evaluasi Program Magang</div>
      </div>

      <div class="info-box">
        <table class="info-table">
          <tr>
            <td class="label">Nama</td>
            <td class="value">: {{ $evaluation->intern->name }}</td>
            <td class="label">Periode</td>
            <td class="value">: {{ $internship->start_date->format('d M Y') }} - {{ $internship->end_date->format('d M Y') }}</td>
          </tr>
          <tr>
            <td class="label">Posisi</td>
            <td class="value">: {{ $internship->custom_position ?? $internship->job?->title ?? 'Intern' }}</td>
            <td class="label">No. Sertifikat</td>
            <td class="value">: CERT/{{ strtoupper(substr($evaluation->id, 0, 8)) }}/{{ now()->format('Y') }}</td>
          </tr>
        </table>
      </div>

      <table class="score-table">
        <thead>
          <tr>
            <th>Komponen Penilaian</th>
            <th>Nilai</th>
          </tr>
        </thead>
        <tbody>
          <tr class="section-header">
            <td colspan="2">Kriteria Standar</td>
          </tr>
          @foreach($standardCriteria as $item)
          <tr>
            <td>{{ $item['name'] }}</td>
            <td>{{ $item['rating'] }}</td>
          </tr>
          @endforeach

          @if(count($customCriteria) > 0)
          <tr class="section-header">
            <td colspan="2">Kriteria Tambahan</td>
          </tr>
          @foreach($customPage2 as $criteria)
          <tr>
            <td>{{ $criteria['name'] }}</td>
            <td>{{ $criteria['rating'] }}</td>
          </tr>
          @endforeach
          @endif

          @if(count($overflowChunks) == 0)
          <tr class="total-row">
            <td>NILAI AKHIR</td>
            <td>{{ $evaluation->overall_rating }}</td>
          </tr>
          @endif
        </tbody>
      </table>

      @if(count($overflowChunks) == 0)
      <div class="legend">
        <span class="legend-item legend-exc">90-100 Sangat Baik</span>
        <span class="legend-item legend-good">80-89 Baik</span>
        <span class="legend-item legend-fair">70-79 Cukup</span>
        <span class="legend-item legend-poor">&lt;70 Kurang</span>
      </div>

      <div class="footer-note">
        Dokumen ini diterbitkan secara elektronik oleh sistem SINTESIS<br>
        Verifikasi keaslian: sintesis.inosoft.co.id/verify/{{ strtoupper(substr($evaluation->id, 0, 8)) }}
      </div>
      @endif
    </div>
  </div>

  @foreach($overflowChunks as $chunkIndex => $chunk)
  <div class="page">
    <div class="details-page">
      <div class="details-header">
        <div class="details-title">LAMPIRAN PENILAIAN (Lanjutan)</div>
        <div class="details-subtitle">Halaman {{ $chunkIndex + 3 }} dari {{ count($overflowChunks) + 2 }}</div>
      </div>

      <table class="score-table">
        <thead>
          <tr>
            <th>Komponen Penilaian</th>
            <th>Nilai</th>
          </tr>
        </thead>
        <tbody>
          <tr class="section-header">
            <td colspan="2">Kriteria Tambahan (Lanjutan)</td>
          </tr>
          @foreach($chunk as $criteria)
          <tr>
            <td>{{ $criteria['name'] }}</td>
            <td>{{ $criteria['rating'] }}</td>
          </tr>
          @endforeach

          @if($loop->last)
          <tr class="total-row">
            <td>NILAI AKHIR</td>
            <td>{{ $evaluation->overall_rating }}</td>
          </tr>
          @endif
        </tbody>
      </table>

      @if($loop->last)
      <div class="legend">
        <span class="legend-item legend-exc">90-100 Sangat Baik</span>
        <span class="legend-item legend-good">80-89 Baik</span>
        <span class="legend-item legend-fair">70-79 Cukup</span>
        <span class="legend-item legend-poor">&lt;70 Kurang</span>
      </div>

      <div class="footer-note">
        Dokumen ini diterbitkan secara elektronik oleh sistem SINTESIS<br>
        Verifikasi keaslian: sintesis.inosoft.co.id/verify/{{ strtoupper(substr($evaluation->id, 0, 8)) }}
      </div>
      @endif
    </div>
  </div>
  @endforeach

</body>

</html>