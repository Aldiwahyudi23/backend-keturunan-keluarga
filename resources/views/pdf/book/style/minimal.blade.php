<style>
@page {
    margin: 1.5cm 2cm 2cm 2cm;
}

* {
    box-sizing: border-box;
}

html, body {
    font-family: "Helvetica Neue", Arial, "sans-serif";
    font-size: 11pt;
    line-height: 1.65;
    color: #333;
}

body {
    margin: 0;
    padding: 0;
}

h1, h2, h3, h4, h5, h6 {
    margin: 0;
    padding: 0;
    font-weight: 600;
    line-height: 1.25;
}

p {
    margin: 0 0 10px;
    line-height: 1.65;
}

table {
    width: 100%;
    border-collapse: collapse;
}

img {
    max-width: 100%;
}

a {
    color: #333;
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
    margin: 4px 0 20px;
    text-align: center;
    font-size: 15pt;
    font-weight: 600;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    color: #222;
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
.bold { font-weight: 600; }
.italic { font-style: italic; }
.uppercase { text-transform: uppercase; }
.small { font-size: 9pt; }
.large { font-size: 13pt; }

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
}

.cover-logo {
    width: 300px;
    height: auto;
    margin: 0 auto 16px;
}

.cover-title {
    font-size: 20pt;
    font-weight: 600;
    text-transform: uppercase;
    line-height: 1.2;
    letter-spacing: 2px;
    color: #222;
}

.cover-subtitle {
    margin-top: 6px;
    font-size: 12pt;
    letter-spacing: 3px;
    color: #888;
    text-transform: uppercase;
    font-weight: 300;
}

.cover-person {
    margin-top: 20px;
}

.cover-name {
    font-size: 28pt;
    font-weight: 600;
    text-transform: uppercase;
    line-height: 1.1;
    letter-spacing: 1px;
}

.cover-nasab {
    margin-top: 6px;
    font-size: 13pt;
    font-style: italic;
    color: #888;
}

.cover-father {
    margin-top: 2px;
    font-size: 16pt;
    font-weight: 500;
    text-transform: uppercase;
    color: #555;
}

.cover-divider {
    width: 60%;
    margin: 20px auto;
    border-top: 1px solid #ccc;
}

.cover-quote {
    width: 80%;
    margin: 0 auto;
    font-size: 12pt;
    font-style: italic;
    line-height: 1.6;
    color: #666;
}

.cover-footer {
    margin-top: 28px;
}

.cover-footer-text {
    font-size: 10pt;
    color: #888;
    line-height: 1.5;
}

.cover-year {
    margin-top: 8px;
    font-size: 14pt;
    letter-spacing: 3px;
    font-weight: 300;
}

.toc-table {
    width: 100%;
    margin-top: 16px;
}

.toc-item td {
    padding: 6px 0;
    border-bottom: 1px solid #eee;
}

.toc-title {
    font-weight: 500;
    font-size: 11pt;
}

.toc-dots {
    text-align: center;
    color: #ccc;
    font-size: 8pt;
    letter-spacing: 1px;
}

.toc-page {
    text-align: right;
    font-weight: 500;
    padding-left: 10px;
    color: #555;
}

.toc-child td {
    padding: 3px 0 3px 20px;
    font-size: 10pt;
    border: none;
}

.toc-title-child {
    font-style: italic;
    color: #666;
}

.toc-total {
    font-size: 9pt;
    color: #999;
    font-style: normal;
}

.history-profile {
    margin-bottom: 20px;
    padding: 16px;
    background: #f9f9f9;
}

.history-table td {
    padding: 3px 0;
    vertical-align: top;
}

.history-section-title {
    font-size: 12pt;
    font-weight: 600;
    margin: 20px 0 12px;
    padding-bottom: 4px;
    border-bottom: 1px solid #ddd;
    color: #333;
}

.history-item {
    margin-bottom: 16px;
    padding-left: 12px;
    border-left: 2px solid #333;
}

.history-item-title {
    font-weight: 600;
    font-size: 11pt;
}

.history-item-date {
    font-style: italic;
    color: #888;
    font-size: 10pt;
    margin: 2px 0 6px;
}

.history-item-content {
    text-align: justify;
}

.history-empty {
    font-style: italic;
    color: #888;
    text-align: center;
    padding: 20px 0;
}

.generation {
    margin-bottom: 24px;
}

.generation-title {
    font-size: 13pt;
    font-weight: 600;
    margin: 8px 0 16px;
    padding-bottom: 4px;
    border-bottom: 2px solid #333;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.member-card {
    margin-bottom: 16px;
    padding: 12px;
    border: 1px solid #e0e0e0;
    page-break-inside: avoid;
}

.root-card {
    border: 1px solid #999;
    background-color: #fafafa;
}

.member-heading {
    margin-bottom: 8px;
}

.member-number {
    font-weight: 600;
    margin-right: 6px;
    font-size: 11pt;
}

.member-name {
    font-weight: 600;
    font-size: 11pt;
}

.member-table td {
    padding: 2px 2px 2px 0;
    vertical-align: top;
}

.member-table td:first-child {
    width: 100px;
    color: #666;
}

.member-table td:nth-child(2) {
    width: 15px;
    text-align: center;
}

.member-photo {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border: 1px solid #e0e0e0;
}

.member-bio {
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px solid #eee;
    text-align: justify;
    font-style: italic;
}

.member-children {
    margin-top: 8px;
    padding-top: 8px;
    border-top: 1px solid #eee;
}

.children-title {
    font-weight: 600;
    font-size: 10pt;
    margin-bottom: 4px;
}

.children-list {
    margin: 4px 0 0;
    padding-left: 20px;
}

.children-list li {
    margin-bottom: 2px;
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
