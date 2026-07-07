<style>
    /* //Resources/views/pdf/book/style/classic.blade.php */
@page {
    margin: 1.5cm 2cm 2cm 3cm;
}

/* =====================================================
   GLOBAL
===================================================== */
* {
    box-sizing: border-box;
}

html, body {
    font-family: "Times New Roman", serif;
    font-size: 12pt;
    line-height: 1.75;
    color: #222;
}

body {
    margin: 0;
    padding: 0;
}

h1, h2, h3, h4, h5, h6 {
    margin: 0;
    padding: 0;
    font-weight: bold;
    line-height: 1.3;
}

p {
    margin: 0 0 12px;
    line-height: 1.75;
}

table {
    width: 100%;
    border-collapse: collapse;
}

img {
    max-width: 100%;
}

a {
    color: #222;
    text-decoration: none;
}

/* =====================================================
   PAGE BREAK
===================================================== */
.page-break {
    page-break-after: always;
}

.page-break-before {
    page-break-before: always;
}

.page-break-inside {
    page-break-inside: avoid;
}

/* =====================================================
   CHAPTER
===================================================== */
.chapter {
    width: 100%;
}

.chapter-title {
    margin: 8px 0 24px;
    text-align: center;
    font-size: 17pt;
    font-weight: bold;
    line-height: 1.3;
}

.chapter-content {
    text-align: justify;
}

.chapter-content p {
    text-indent: 36px;
    margin-bottom: 12px;
}

.chapter-content p:first-child {
    text-indent: 0;
}

/* =====================================================
   COMMON TABLE
===================================================== */
.table {
    width: 100%;
    border-collapse: collapse;
}

.table td {
    vertical-align: top;
    padding: 4px 0;
}

/* =====================================================
   COMMON IMAGE
===================================================== */
.image-center {
    text-align: center;
}

.image-circle {
    border-radius: 50%;
}

/* =====================================================
   UTILITIES
===================================================== */
.text-left { text-align: left; }
.text-center { text-align: center; }
.text-right { text-align: right; }
.bold { font-weight: bold; }
.italic { font-style: italic; }
.uppercase { text-transform: uppercase; }
.small { font-size: 10pt; }
.large { font-size: 15pt; }

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

/* =====================================================
   COVER
===================================================== */
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
    width: 400px;
    height: auto;
    margin: 0 auto 18px;
}

.cover-title {
    font-size: 24pt;
    font-weight: bold;
    text-transform: uppercase;
    line-height: 1.25;
    letter-spacing: 1px;
}

.cover-subtitle {
    margin-top: 8px;
    font-size: 15pt;
    letter-spacing: 5px;
    color: #666;
    text-transform: uppercase;
}

.cover-person {
    margin-top: 22px;
}

.cover-name {
    font-size: 30pt;
    font-weight: bold;
    color: #9b7a2d;
    text-transform: uppercase;
    line-height: 1.15;
}

.cover-nasab {
    margin-top: 8px;
    font-size: 15pt;
    font-style: italic;
    color: #666;
}

.cover-father {
    margin-top: 3px;
    font-size: 18pt;
    font-weight: bold;
    text-transform: uppercase;
}

.cover-divider {
    width: 72%;
    margin: 24px auto;
    border-top: 1px solid #999;
}

.cover-quote {
    width: 78%;
    margin: 0 auto;
    font-size: 14pt;
    font-style: italic;
    line-height: 1.65;
    color: #444;
}

.cover-footer {
    margin-top: 30px;
}

.cover-footer-text {
    font-size: 11pt;
    color: #666;
    line-height: 1.5;
}

.cover-year {
    margin-top: 10px;
    font-size: 16pt;
    letter-spacing: 2px;
    font-weight: bold;
}

/* =====================================================
   TOC
===================================================== */
.toc-table {
    width: 100%;
    margin-top: 20px;
}

.toc-item td {
    padding: 4px 0;
}

.toc-title {
    font-weight: bold;
    font-size: 12pt;
}

.toc-dots {
    text-align: center;
    color: #999;
    font-size: 10pt;
    letter-spacing: 1px;
}

.toc-page {
    text-align: right;
    font-weight: bold;
    padding-left: 10px;
}

.toc-child td {
    padding: 2px 0 2px 20px;
    font-size: 11pt;
}

.toc-title-child {
    font-style: italic;
}

.toc-total {
    font-size: 10pt;
    color: #666;
    font-style: normal;
}

/* =====================================================
   HISTORY
===================================================== */
.history-profile {
    margin-bottom: 20px;
}

.history-table td {
    padding: 4px 0;
    vertical-align: top;
}

.history-section-title {
    font-size: 14pt;
    font-weight: bold;
    margin: 20px 0 15px;
    border-bottom: 1px solid #ddd;
    padding-bottom: 5px;
}

.history-item {
    margin-bottom: 20px;
    padding-left: 10px;
    border-left: 3px solid #9b7a2d;
}

.history-item-title {
    font-weight: bold;
    font-size: 13pt;
}

.history-item-date {
    font-style: italic;
    color: #666;
    font-size: 11pt;
    margin: 2px 0 8px;
}

.history-item-content {
    text-align: justify;
}

.history-empty {
    font-style: italic;
    color: #666;
    text-align: center;
    padding: 20px 0;
}

/* =====================================================
   GENEALOGY
===================================================== */
.generation {
    margin-bottom: 30px;
}

.generation-title {
    font-size: 15pt;
    font-weight: bold;
    margin: 10px 0 20px;
    padding-bottom: 5px;
    border-bottom: 2px solid #9b7a2d;
}

.member-card {
    margin-bottom: 20px;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    page-break-inside: avoid;
}

.root-card {
    border-color: #9b7a2d;
    background-color: #fdf9f0;
}

.member-heading {
    margin-bottom: 10px;
}

.member-number {
    font-weight: bold;
    color: #9b7a2d;
    margin-right: 8px;
    font-size: 13pt;
}

.member-name {
    font-weight: bold;
    font-size: 13pt;
}

.member-table td {
    padding: 2px 2px 2px 0;
    vertical-align: top;
}

.member-table td:first-child {
    width: 120px;
    color: #555;
}

.member-table td:nth-child(2) {
    width: 15px;
    text-align: center;
}

.member-photo {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 50%;
    border: 2px solid #ddd;
}

.member-bio {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px dashed #ddd;
    text-align: justify;
    font-style: italic;
}

.member-children {
    margin-top: 10px;
    padding-top: 10px;
    border-top: 1px dashed #ddd;
}

.children-title {
    font-weight: bold;
    font-size: 11pt;
    margin-bottom: 5px;
    color: #555;
}

.children-list {
    margin: 5px 0 0;
    padding-left: 25px;
}

.children-list li {
    margin-bottom: 3px;
    line-height: 1.5;
}

/* =====================================================
   RESPONSIVE FIXES
===================================================== */
@media print {
    .page-break {
        page-break-after: always;
    }
    
    .member-card {
        page-break-inside: avoid;
    }
}
</style>