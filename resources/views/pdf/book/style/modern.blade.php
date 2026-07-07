<style>
@page {
    margin: 2cm 2cm 2.5cm 2cm;
}

* {
    box-sizing: border-box;
}

html, body {
    font-family: "Segoe UI", "Helvetica Neue", Arial, sans-serif;
    font-size: 11pt;
    line-height: 1.7;
    color: #2c3e50;
}

body {
    margin: 0;
    padding: 0;
}

h1, h2, h3, h4, h5, h6 {
    margin: 0;
    padding: 0;
    font-weight: 700;
    line-height: 1.3;
}

p {
    margin: 0 0 10px;
    line-height: 1.7;
}

table {
    width: 100%;
    border-collapse: collapse;
}

img {
    max-width: 100%;
}

a {
    color: #3498db;
    text-decoration: none;
}

.page-break {
    page-break-after: always;
}

.page-break-before {
    page-break-before: always;
}

.page-break-inside {
    page-break-inside: avoid;
}

.chapter {
    width: 100%;
}

.chapter-title {
    margin: 0 0 28px;
    text-align: center;
    font-size: 18pt;
    font-weight: 700;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    color: #2c3e50;
    position: relative;
    padding-bottom: 14px;
}

.chapter-title::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 60px;
    height: 3px;
    background: #3498db;
}

.chapter-content {
    text-align: justify;
}

.chapter-content p {
    margin-bottom: 10px;
}

.text-left { text-align: left; }
.text-center { text-align: center; }
.text-right { text-align: right; }
.bold { font-weight: 700; }
.italic { font-style: italic; }
.uppercase { text-transform: uppercase; }
.small { font-size: 9pt; }
.large { font-size: 14pt; }

.mt-0 { margin-top: 0; }
.mt-1 { margin-top: 6px; }
.mt-2 { margin-top: 12px; }
.mt-3 { margin-top: 18px; }
.mt-4 { margin-top: 24px; }

.mb-0 { margin-bottom: 0; }
.mb-1 { margin-bottom: 6px; }
.mb-2 { margin-bottom: 12px; }
.mb-3 { margin-bottom: 18px; }
.mb-4 { margin-bottom: 24px; }

.cover {
    position: relative;
    height: 100%;
    text-align: center;
    display: flex;
    flex-direction: column;
    justify-content: center;
    min-height: 100vh;
    background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
    color: #fff;
    padding: 40px;
    margin: -2cm;
    width: calc(100% + 4cm);
}

.cover-logo {
    width: 220px;
    height: auto;
    margin: 0 auto 20px;
    filter: brightness(0) invert(1);
}

.cover-title {
    font-size: 22pt;
    font-weight: 700;
    text-transform: uppercase;
    line-height: 1.25;
    letter-spacing: 3px;
    color: rgba(255,255,255,0.9);
}

.cover-subtitle {
    margin-top: 8px;
    font-size: 12pt;
    letter-spacing: 4px;
    color: rgba(255,255,255,0.7);
    text-transform: uppercase;
    font-weight: 300;
}

.cover-person {
    margin-top: 30px;
}

.cover-name {
    font-size: 32pt;
    font-weight: 700;
    text-transform: uppercase;
    line-height: 1.1;
    letter-spacing: 2px;
    color: #fff;
}

.cover-nasab {
    margin-top: 8px;
    font-size: 13pt;
    font-style: italic;
    color: rgba(255,255,255,0.7);
}

.cover-father {
    margin-top: 4px;
    font-size: 18pt;
    font-weight: 500;
    text-transform: uppercase;
    color: rgba(255,255,255,0.85);
}

.cover-divider {
    width: 80px;
    margin: 24px auto;
    border-top: 2px solid rgba(255,255,255,0.5);
}

.cover-quote {
    width: 75%;
    margin: 0 auto;
    font-size: 11pt;
    font-style: italic;
    line-height: 1.7;
    color: rgba(255,255,255,0.75);
}

.cover-footer {
    margin-top: 32px;
}

.cover-footer-text {
    font-size: 10pt;
    color: rgba(255,255,255,0.6);
    line-height: 1.5;
}

.cover-year {
    margin-top: 10px;
    font-size: 16pt;
    letter-spacing: 4px;
    font-weight: 300;
    color: rgba(255,255,255,0.8);
}

.toc-table {
    width: 100%;
    margin-top: 20px;
}

.toc-item td {
    padding: 8px 0;
    border-bottom: 1px solid #e9ecef;
}

.toc-title {
    font-weight: 600;
    font-size: 11pt;
    color: #2c3e50;
}

.toc-dots {
    text-align: center;
    color: #ced4da;
    font-size: 8pt;
    letter-spacing: 1px;
}

.toc-page {
    text-align: right;
    font-weight: 600;
    padding-left: 10px;
    color: #3498db;
    font-size: 11pt;
}

.toc-child td {
    padding: 4px 0 4px 24px;
    font-size: 10pt;
    border: none;
}

.toc-title-child {
    font-style: italic;
    color: #6c757d;
}

.toc-total {
    font-size: 9pt;
    color: #adb5bd;
    font-style: normal;
}

.history-profile {
    margin-bottom: 24px;
    padding: 20px;
    background: #f8f9fa;
    border-left: 4px solid #3498db;
    border-radius: 4px;
}

.history-table td {
    padding: 4px 0;
    vertical-align: top;
}

.history-section-title {
    font-size: 13pt;
    font-weight: 700;
    margin: 24px 0 14px;
    padding-bottom: 6px;
    border-bottom: 2px solid #3498db;
    color: #2c3e50;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.history-item {
    margin-bottom: 18px;
    padding: 14px 16px;
    background: #f8f9fa;
    border-radius: 4px;
    page-break-inside: avoid;
}

.history-item-title {
    font-weight: 700;
    font-size: 11pt;
    color: #2c3e50;
}

.history-item-date {
    font-style: italic;
    color: #6c757d;
    font-size: 10pt;
    margin: 2px 0 6px;
}

.history-item-content {
    text-align: justify;
}

.history-empty {
    font-style: italic;
    color: #6c757d;
    text-align: center;
    padding: 24px 0;
}

.generation {
    margin-bottom: 28px;
}

.generation-title {
    font-size: 14pt;
    font-weight: 700;
    margin: 10px 0 18px;
    padding-bottom: 6px;
    border-bottom: 3px solid #3498db;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: #2c3e50;
}

.member-card {
    margin-bottom: 18px;
    padding: 16px;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    page-break-inside: avoid;
    background: #fff;
}

.root-card {
    border: 1px solid #3498db;
    background: #f0f7ff;
}

.member-heading {
    margin-bottom: 10px;
    padding-bottom: 6px;
    border-bottom: 1px solid #e9ecef;
}

.member-number {
    font-weight: 700;
    margin-right: 6px;
    font-size: 11pt;
    color: #3498db;
}

.member-name {
    font-weight: 700;
    font-size: 12pt;
    color: #2c3e50;
}

.member-table td {
    padding: 3px 3px 3px 0;
    vertical-align: top;
}

.member-table td:first-child {
    width: 110px;
    color: #6c757d;
}

.member-table td:nth-child(2) {
    width: 15px;
    text-align: center;
}

.member-photo {
    width: 90px;
    height: 90px;
    object-fit: cover;
    border: 2px solid #e9ecef;
    border-radius: 4px;
}

.member-bio {
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid #e9ecef;
    text-align: justify;
    font-style: italic;
    color: #6c757d;
}

.member-children {
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid #e9ecef;
}

.children-title {
    font-weight: 700;
    font-size: 10pt;
    margin-bottom: 4px;
    color: #3498db;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.children-list {
    margin: 4px 0 0;
    padding-left: 20px;
}

.children-list li {
    margin-bottom: 3px;
    line-height: 1.5;
}

@media print {
    .page-break {
        page-break-after: always;
    }

    .member-card {
        page-break-inside: avoid;
    }
}
</style>
