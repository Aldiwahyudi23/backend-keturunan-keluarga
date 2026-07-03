<style>

    @page {
        margin-top: 1.5cm;
        margin-right: 2.0cm;
        margin-bottom: 2.0cm;
        margin-left: 3.0cm;
    }

    body {
        font-family: "Times New Roman", serif;
        font-size: 12pt;
        line-height: 1.8;
        color: #222;
    }

    * {
        box-sizing: border-box;
    }

    h1,
    h2,
    h3,
    h4,
    h5,
    h6 {
        margin: 0;
        padding: 0;
    }

    p {
        margin: 0 0 12px;
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
        text-align: center;
        font-size: 17pt;
        font-weight: bold;
        margin-top: 10px;
        margin-bottom: 20px;
    }

    .chapter-content {
        text-align: justify;
        line-height: 1.9;
        font-size: 12pt;
    }

    .chapter-content p {
        text-indent: 40px;
        margin-bottom: 14px;
    }

    .chapter-content p:first-child {
        text-indent: 0;
    }

    /* =====================================================
       COVER
    ===================================================== */

.cover{
    height:100%;
    text-align:center;
    position:relative;
}

.cover-logo{
    width:120px;
    height:auto;
    margin-top:20px;
    margin-bottom:45px;
}

.cover-title{

    font-size:28pt;

    font-weight:bold;

    letter-spacing:2px;

    text-transform:uppercase;

    line-height:1.3;

}

.cover-subtitle{

    font-size:16pt;

    letter-spacing:8px;

    color:#666;

    margin-top:12px;

}

.cover-name{

    font-size:30pt;

    font-weight:bold;

    margin-top:18px;

}

.cover-nasab{

    font-size:16pt;

    font-style:italic;

    color:#777;

    margin-top:10px;

}

.cover-father{

    font-size:22pt;

    font-weight:normal;

}

.cover-divider{

    width:180px;

    border-top:1px solid #999;

    margin:28px auto;

}

.cover-quote{

    width:80%;

    margin:0 auto;

    font-size:13pt;

    line-height:1.9;

    font-style:italic;

    color:#555;
}

.cover-footer{

    position:absolute;

    bottom:30px;

    width:100%;

}

.cover-website{

    font-size:11pt;

    color:#666;

}

.cover-year{

    margin-top:12px;

    font-size:18pt;

    letter-spacing:2px;

}

/*==================================================
    TABLE OF CONTENTS
==================================================*/

.toc-table{
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
}

.toc-table td{
    padding:8px 0;
    vertical-align:top;
}

.toc-item{
    font-size:13pt;
}

.toc-title{
    width:35%;
}

.toc-dots{
    width:55%;
    color:#777;
    white-space:nowrap;
    overflow:hidden;
}

.toc-page{
    width:10%;
    text-align:right;
}

.toc-child td{
    padding:4px 0;
}

.toc-title-child{
    padding-left:35px;
    font-size:11.5pt;
}

.toc-total{
    color:#666;
    font-size:10pt;
    font-style:italic;
}

/* ===========================================
   ROOT PERSON
=========================================== */

.member-root{
    border-bottom:none;
}

.root-photo-wrapper{
    text-align:center;
    margin:30px 0 22px;
}

.root-photo{

    width:150px;
    height:150px;

    border-radius:50%;

    object-fit:cover;

    border:4px solid #d7d7d7;
}

.root-bio{

    margin-top:20px;

    padding-top:18px;

    border-top:1px solid #d8d8d8;
}

.root-bio h3{

    font-size:13pt;

    margin-bottom:10px;

    font-weight:bold;
}

.root-bio p{

    text-align:justify;

    line-height:1.8;

    text-indent:35px;
}

/* =====================================================
   GENEALOGY
===================================================== */

.generation{
    width:100%;
}

.generation-title{
    text-align:inline;
    font-size:15pt;
    font-weight:bold;
    margin:0 0 18px;
}

.member{
    margin:0 0 18px;
    padding:0 0 18px;
    border-bottom:1px solid #d9d9d9;
    page-break-inside:avoid;
}

.member:last-child{
    border-bottom:none;
}

.member-number{
    display:inline;
    font-size:13pt;
    font-weight:bold;
}

.member-name{
    display:inline;
    font-size:13pt;
    font-weight:bold;
    margin-left:6px;
}

.member-info{
    margin-top:10px;
    margin-left:18px;
    line-height:1.6;
}

.member-info div{
    margin-bottom:3px;
}

.member-info .label{
    display:inline-block;
    width:120px;
}

.member-info .value{
    display:inline-block;
}

.member-table{
    width:auto;
    border-collapse:collapse;
    margin-top:10px;
    margin-left:18px;
}

.member-table tr{
    height:auto;
}

.member-table td{
    padding:2px 0;
    line-height:1.5;
    vertical-align:top;
}

.member-table td:first-child{
    width:120px;
    font-weight:normal;
}

.member-table td:nth-child(2){
    width:15px;
    text-align:center;
}

.member-table td:last-child{
    width:auto;
}

.children-title{
    margin:10px 0 4px 18px;
    font-weight:bold;
}

.children-list{
    margin:0 0 0 38px;
    padding:0;
}

.children-list li{
    margin:0;
    padding:2px 0;
    line-height:1.5;
}

.member + .member{
    margin-top:22px;
}

/* ======================================================
   HISTORY
====================================================== */

.history-profile{
    margin-bottom:30px;
}

.history-table{
    width:100%;
    border-collapse:collapse;
}

.history-table td{
    padding:4px 0;
    vertical-align:top;
}

.history-section-title{
    margin-top:35px;
    margin-bottom:20px;
    font-size:18pt;
    font-weight:bold;
    border-bottom:1px solid #999;
    padding-bottom:6px;
}

.history-bio{
    margin-top:25px;
    margin-bottom:35px;
    text-align:justify;
    line-height:1.8;
}

.history-bio p{
    text-indent:35px;
    margin-bottom:12px;
}

.history-item{
    margin-bottom:30px;
    page-break-inside:avoid;
}

.history-item-title{
    font-size:15pt;
    font-weight:bold;
    margin-bottom:5px;
}

.history-item-date{
    font-style:italic;
    color:#666;
    margin-bottom:10px;
}

.history-item-content{
    text-align:justify;
    line-height:1.8;
}

.history-item-content p{
    text-indent:35px;
    margin-bottom:12px;
}

.history-empty{
    margin-top:40px;
    text-align:center;
    font-style:italic;
    color:#777;
}

    /* =====================================================
       UTILITIES
    ===================================================== */

    .text-center {
        text-align: center;
    }

    .text-right {
        text-align: right;
    }

    .text-left {
        text-align: left;
    }

    .bold {
        font-weight: bold;
    }

    .italic {
        font-style: italic;
    }

</style>