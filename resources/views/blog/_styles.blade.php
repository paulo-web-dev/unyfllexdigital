@push('styles')
<style>
  .blog-wrap{max-width:1180px;margin:0 auto;padding:0 20px}
  .blog-hero{padding:64px 0 24px;text-align:center}
  .blog-hero h1{font-family:var(--font-display);font-weight:800;font-size:clamp(32px,5vw,52px);color:var(--fg-1);letter-spacing:-.02em;margin:0 0 14px}
  .blog-hero p{font-size:18px;color:var(--fg-3);max-width:660px;margin:0 auto;line-height:1.55}
  .blog-cats{display:flex;flex-wrap:wrap;gap:10px;justify-content:center;margin-top:28px}
  .blog-cat-pill{display:inline-flex;align-items:center;gap:7px;padding:8px 16px;border-radius:var(--r-pill);border:1px solid var(--line-2);background:var(--bg-2);color:var(--fg-2);font-size:14px;font-weight:500;text-decoration:none;transition:.18s}
  .blog-cat-pill:hover,.blog-cat-pill.active{border-color:var(--line-3);background:var(--bg-3);color:#fff}
  .blog-cat-pill .n{font-size:12px;color:var(--fg-4)}

  .blog-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:26px;margin:36px 0 40px}
  @media(max-width:900px){.blog-grid{grid-template-columns:repeat(2,1fr)}}
  @media(max-width:600px){.blog-grid{grid-template-columns:1fr}}

  .post-card{display:flex;flex-direction:column;background:var(--bg-2);border:1px solid var(--line-1);border-radius:var(--r-lg);overflow:hidden;transition:.2s;text-decoration:none}
  .post-card:hover{transform:translateY(-4px);border-color:var(--line-3);box-shadow:0 18px 40px -20px rgba(0,163,255,.4)}
  .post-card .thumb{aspect-ratio:16/9;background:linear-gradient(135deg,var(--brand-800),var(--brand-900));position:relative;display:flex;align-items:flex-end;padding:16px;overflow:hidden}
  .post-card .thumb img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
  .post-card .thumb .badge-cat{position:relative;z-index:2;background:rgba(5,8,15,.7);color:#7fd0ff;font-size:12px;font-weight:600;padding:5px 11px;border-radius:var(--r-pill);border:1px solid var(--line-3)}
  .post-card .body{padding:20px;display:flex;flex-direction:column;flex:1}
  .post-card .body h3{font-family:var(--font-display);font-weight:700;font-size:19px;line-height:1.3;color:var(--fg-1);margin:0 0 10px}
  .post-card .body p{font-size:14px;color:var(--fg-3);line-height:1.55;margin:0 0 16px;flex:1}
  .post-card .meta{display:flex;align-items:center;gap:14px;font-size:12px;color:var(--fg-4);border-top:1px solid var(--line-1);padding-top:13px}
  .post-card .meta span{display:inline-flex;align-items:center;gap:5px}
  .post-card .meta svg{width:14px;height:14px}

  .blog-pag{display:flex;justify-content:center;margin:10px 0 64px}
  .blog-pag .pagination{--bs-pagination-bg:var(--bg-2);--bs-pagination-border-color:var(--line-2);--bs-pagination-color:var(--fg-2);--bs-pagination-hover-bg:var(--bg-3);--bs-pagination-hover-border-color:var(--line-3);--bs-pagination-hover-color:#fff;--bs-pagination-active-bg:var(--brand-500);--bs-pagination-active-border-color:var(--brand-500);--bs-pagination-disabled-bg:var(--bg-1);--bs-pagination-disabled-color:var(--fg-4)}

  .blog-bc{max-width:980px;margin:0 auto;padding:24px 20px 0;font-size:13px;color:var(--fg-4)}
  .blog-bc a{color:var(--fg-3);text-decoration:none}
  .blog-bc a:hover{color:#fff}

  .post-head{max-width:820px;margin:0 auto;padding:18px 20px 0;text-align:center}
  .post-head .badge-cat{background:var(--bg-2);color:#7fd0ff;font-size:13px;font-weight:600;padding:6px 14px;border-radius:var(--r-pill);border:1px solid var(--line-3);text-decoration:none}
  .post-head h1{font-family:var(--font-display);font-weight:800;font-size:clamp(28px,4.5vw,46px);line-height:1.15;letter-spacing:-.02em;color:var(--fg-1);margin:18px 0}
  .post-head .meta{display:flex;flex-wrap:wrap;justify-content:center;gap:8px 20px;font-size:14px;color:var(--fg-4)}
  .post-head .meta span{display:inline-flex;align-items:center;gap:6px}
  .post-head .meta svg{width:15px;height:15px}
  .post-featured{max-width:980px;margin:34px auto 0;padding:0 20px}
  .post-featured img{width:100%;border-radius:var(--r-lg);border:1px solid var(--line-2)}

  .post-body{max-width:760px;margin:0 auto;padding:40px 20px 0;font-size:18px;line-height:1.75;color:var(--fg-2)}
  .post-body h2{font-family:var(--font-display);font-weight:700;font-size:28px;color:var(--fg-1);margin:42px 0 16px;letter-spacing:-.01em}
  .post-body h3{font-family:var(--font-display);font-weight:600;font-size:22px;color:var(--fg-1);margin:32px 0 12px}
  .post-body p{margin:0 0 20px}
  .post-body ul,.post-body ol{margin:0 0 20px;padding-left:24px}
  .post-body li{margin:0 0 9px}
  .post-body a{color:var(--brand-300);text-decoration:underline;text-underline-offset:2px}
  .post-body img{max-width:100%;border-radius:var(--r-md);margin:24px 0}
  .post-body blockquote{border-left:3px solid var(--brand-500);padding:6px 0 6px 20px;margin:24px 0;color:var(--fg-3);font-style:italic}
  .post-body strong{color:var(--fg-1)}

  .post-faq{max-width:760px;margin:48px auto 0;padding:0 20px}
  .post-faq h2{font-family:var(--font-display);font-weight:700;font-size:26px;color:var(--fg-1);margin:0 0 20px}
  .faq-item{border:1px solid var(--line-2);border-radius:var(--r-md);background:var(--bg-2);margin-bottom:12px;overflow:hidden}
  .faq-item summary{cursor:pointer;padding:16px 20px;font-weight:600;color:var(--fg-1);font-size:16px;list-style:none;display:flex;justify-content:space-between;gap:12px;align-items:center}
  .faq-item summary::-webkit-details-marker{display:none}
  .faq-item summary::after{content:"+";color:var(--brand-300);font-size:22px;line-height:1}
  .faq-item[open] summary::after{content:"–"}
  .faq-item .a{padding:0 20px 18px;color:var(--fg-3);line-height:1.6}

  .post-tags{max-width:760px;margin:36px auto 0;padding:0 20px;display:flex;flex-wrap:wrap;gap:9px}
  .post-tags a{font-size:13px;color:var(--fg-3);background:var(--bg-2);border:1px solid var(--line-2);padding:6px 13px;border-radius:var(--r-pill);text-decoration:none}
  .post-tags a:hover{border-color:var(--line-3);color:#fff}

  .mini-cta{max-width:820px;margin:52px auto 0;padding:0 20px}
  .mini-cta-inner{background:linear-gradient(135deg,var(--brand-800),var(--brand-900));border:1px solid var(--line-3);border-radius:var(--r-xl);padding:38px 36px;text-align:center;position:relative;overflow:hidden}
  .mini-cta-inner::before{content:"";position:absolute;width:280px;height:280px;background:radial-gradient(circle,rgba(0,163,255,.25),transparent 70%);top:-120px;right:-60px}
  .mini-cta-inner .k{position:relative;font-size:13px;letter-spacing:.14em;text-transform:uppercase;color:#7fd0ff;font-weight:600;margin-bottom:12px}
  .mini-cta-inner h3{position:relative;font-family:var(--font-display);font-weight:800;font-size:25px;color:#fff;margin:0 0 12px;line-height:1.25}
  .mini-cta-inner p{position:relative;color:#c8e3f7;font-size:16px;margin:0 auto 22px;max-width:520px}
  .mini-cta-inner .btn-mini{position:relative;display:inline-flex;align-items:center;gap:9px;background:#fff;color:#0a2540;font-family:var(--font-display);font-weight:700;font-size:16px;padding:13px 26px;border-radius:var(--r-pill);text-decoration:none;transition:.18s}
  .mini-cta-inner .btn-mini:hover{transform:translateY(-2px);box-shadow:0 14px 30px -10px rgba(0,0,0,.5)}
  .mini-cta-inner .btn-mini svg{width:18px;height:18px}

  .post-rel{max-width:1180px;margin:64px auto 0;padding:0 20px 64px}
  .post-rel h2{font-family:var(--font-display);font-weight:700;font-size:24px;color:var(--fg-1);margin:0 0 24px}

  .arch-head{padding:46px 0 8px;text-align:center}
  .arch-head .k{font-size:13px;letter-spacing:.14em;text-transform:uppercase;color:#7fd0ff;font-weight:600}
  .arch-head h1{font-family:var(--font-display);font-weight:800;font-size:clamp(30px,4.5vw,46px);color:var(--fg-1);margin:12px 0 14px;letter-spacing:-.02em}
  .arch-head p{font-size:17px;color:var(--fg-3);max-width:640px;margin:0 auto;line-height:1.55}

  .blog-empty{text-align:center;padding:80px 20px;color:var(--fg-4)}
</style>
@endpush
