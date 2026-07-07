<style>
@page {
    margin: 2cm 2.5cm 2.5cm 2.5cm;
}

* {
    box-sizing: border-box;
}

html, body {
    font-family: Georgia, "Times New Roman", serif;
    font-size: 11pt;
    line-height: 1.8;
    color: #2c2c2c;
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
    line-height: 1.8;
}

table {
    width: 100%;
    border-collapse: collapse;
}

img {
    max-width: 100%;
}

a {
    color: #c9a84c;
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
    margin: 0 0 32px;
    text-align: center;
    font-size: 18pt;
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
    color: #1a2a3a;
    position: relative;
    padding-bottom: 16px;
}

.chapter-title::before {
    content: '';
    display: block;
    width: 120px;
    height: 1px;
    background: #c9a84c;
    margin: 0 auto 16px;
}

.chapter-title::after {
    content: '';
    display: block;
    width: 60px;
    height: 2px;
    background: #c9a84c;
    margin: 16px auto 0;
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
    background: linear-gradient(180deg, #1a2a3a 0%, #2c3e50 100%);
    color: #fff;
    padding: 40px;
    margin: -2cm -2.5cm;
    width: calc(100% + 5cm);
}

.cover::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 20px;
    right: 20px;
    bottom: 20px;
    border: 1px solid rgba(201, 168, 76, 0.4);
    pointer-events: none;
}

.cover::after {
    content: '';
    position: absolute;
    top: 30px;
    left: 30px;
    right: 30px;
    bottom: 30px;
    border: 1px solid rgba(201, 168, 76, 0.2);
    pointer-events: none;
}

.cover-logo {
    width: 200px;
    height: auto;
    margin: 0 auto 24px;
}

.cover-title {
    font-size: 20pt;
    font-weight: 700;
    text-transform: uppercase;
    line-height: 1.3;
    letter-spacing: 3px;
    color: #c9a84c;
}

.cover-subtitle {
    margin-top: 8px;
    font-size: 11pt;
    letter-spacing: 5px;
    color: rgba(255,255,255,0.6);
    text-transform: uppercase;
    font-weight: 400;
}

.cover-person {
    margin-top: 32px;
}

.cover-name {
    font-size: 30pt;
    font-weight: 700;
    text-transform: uppercase;
    line-height: 1.15;
    letter-spacing: 2px;
    color: #fff;
}

.cover-nasab {
    margin-top: 8px;
    font-size: 13pt;
    font-style: italic;
    color: #c9a84c;
}

.cover-father {
    margin-top: 4px;
    font-size: 16pt;
    font-weight: 400;
    text-transform: uppercase;
    color: rgba(255,255,255,0.8);
    letter-spacing: 1px;
}

.cover-divider {
    width: 100px;
    margin: 28px auto;
    border-top: 1px solid #c9a84c;
}

.cover-quote {
    width: 70%;
    margin: 0 auto;
    font-size: 11pt;
    font-style: italic;
    line-height: 1.8;
    color: rgba(255,255,255,0.7);
}

.cover-footer {
    margin-top: 36px;
}

.cover-footer-text {
    font-size: 10pt;
    color: rgba(255,255,255,0.5);
    line-height: 1.6;
}

.cover-year {
    margin-top: 10px;
    font-size: 14pt;
    letter-spacing: 4px;
    font-weight: 400;
    color: #c9a84c;
}

.toc-table {
    width: 100%;
    margin-top: 24px;
}

.toc-item td {
    padding: 10px 0;
    border-bottom: 1px solid #e8e0d0;
}

.toc-title {
    font-weight: 600;
    font-size: 11pt;
    color: #1a2a3a;
}

.toc-dots {
    text-align: center;
    color: #d4c9a8;
    font-size: 8pt;
    letter-spacing: 1px;
}

.toc-page {
    text-align: right;
    font-weight: 600;
    padding-left: 12px;
    color: #c9a84c;
    font-size: 11pt;
}

.toc-child td {
    padding: 5px 0 5px 28px;
    font-size: 10pt;
    border: none;
}

.toc-title-child {
    font-style: italic;
    color: #8a7a5a;
}

.toc-total {
    font-size: 9pt;
    color: #b8a88a;
    font-style: normal;
}

.history-profile {
    margin-bottom: 28px;
    padding: 24px;
    background: #faf8f3;
    border: 1px solid #e8e0d0;
}

.history-table td {
    padding: 5px 0;
    vertical-align: top;
}

.history-section-title {
    font-size: 13pt;
    font-weight: 700;
    margin: 28px 0 16px;
    padding-bottom: 8px;
    border-bottom: 2px solid #c9a84c;
    color: #1a2a3a;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.history-item {
    margin-bottom: 20px;
    padding: 16px 20px;
    padding-left: 24px;
    border-left: 3px solid #c9a84c;
    page-break-inside: avoid;
    background: #faf8f3;
}

.history-item-title {
    font-weight: 700;
    font-size: 12pt;
    color: #1a2a3a;
}

.history-item-date {
    font-style: italic;
    color: #8a7a5a;
    font-size: 10pt;
    margin: 3px 0 8px;
}

.history-item-content {
    text-align: justify;
}

.history-empty {
    font-style: italic;
    color: #8a7a5a;
    text-align: center;
    padding: 28px 0;
}

.generation {
    margin-bottom: 32px;
}

.generation-title {
    font-size: 14pt;
    font-weight: 700;
    margin: 12px 0 20px;
    padding-bottom: 8px;
    border-bottom: 2px solid #c9a84c;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: #1a2a3a;
}

.member-card {
    margin-bottom: 20px;
    padding: 0;
    page-break-inside: avoid;
}

.member-card-inner {
    width: 100%;
    border: 1px solid #d4c9a8;
}

.member-card-inner td {
    vertical-align: top;
}

.member-card-left {
    padding: 16px;
    border-right: 1px solid #d4c9a8;
    width: 55%;
}

.member-card-right {
    padding: 16px;
    width: 45%;
    background: #faf8f3;
}

.root-card .member-card-left {
    background: #faf8f3;
}

.root-card .member-card-right {
    background: #f5f0e4;
}

.member-heading {
    margin-bottom: 10px;
    padding-bottom: 8px;
    border-bottom: 1px solid #e8e0d0;
}

.member-number {
    font-weight: 700;
    margin-right: 6px;
    font-size: 11pt;
    color: #c9a84c;
}

.member-name {
    font-weight: 700;
    font-size: 12pt;
    color: #1a2a3a;
}

.member-table td {
    padding: 3px 3px 3px 0;
    vertical-align: top;
}

.member-table td:first-child {
    width: 110px;
    color: #8a7a5a;
}

.member-table td:nth-child(2) {
    width: 15px;
    text-align: center;
}

.member-photo {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border: 2px solid #d4c9a8;
}

.right-section {
    margin-bottom: 14px;
}

.right-section:last-child {
    margin-bottom: 0;
}

.right-section-title {
    font-size: 10pt;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: #c9a84c;
    margin-bottom: 6px;
    padding-bottom: 4px;
    border-bottom: 1px solid #e8e0d0;
}

.spouse-name {
    font-weight: 600;
    font-size: 11pt;
    color: #1a2a3a;
    margin-bottom: 2px;
}

.spouse-detail {
    font-size: 10pt;
    color: #8a7a5a;
    font-style: italic;
}

.children-list {
    margin: 4px 0 0;
    padding-left: 18px;
}

.children-list li {
    margin-bottom: 3px;
    line-height: 1.6;
    font-size: 10pt;
}

.member-bio-text {
    margin-top: 8px;
    padding-top: 6px;
    border-top: 1px solid #e8e0d0;
    text-align: justify;
    font-style: italic;
    color: #6a5a4a;
    font-size: 10pt;
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
