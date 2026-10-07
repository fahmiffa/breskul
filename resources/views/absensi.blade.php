<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Tutorial Absensi Breskul</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
<style>
:root{--bg:#e8f3ec;--ink:#12301f;--g:#1faa59;--g2:#0e7a3e;--gl:#e6f6ec;--bl:#2f7df6;--or:#f59e0b;--sh:0 6px 20px rgba(14,90,50,.16);box-sizing:border-box;padding-top:env(safe-area-inset-top,0px);padding-bottom:env(safe-area-inset-bottom,0px)}
@media(prefers-color-scheme:dark){:root:not([data-theme=light]){--bg:#0d1c14}}
*{box-sizing:border-box;margin:0}
html,body{height:100%}
body{background:var(--bg);font-family:'Plus Jakarta Sans',system-ui,sans-serif;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:10px;overflow:hidden;color:var(--ink)}
#wrap{width:360px;height:640px;transform-origin:top center;position:relative}
#holder{position:relative;overflow:hidden}
#stage{width:360px;height:640px;position:absolute;left:0;top:0;transform-origin:top left;background:#fff;border-radius:22px;overflow:hidden;box-shadow:0 14px 40px rgba(0,0,0,.25)}
.sc{position:absolute;inset:0;opacity:0;transform:translateX(40px) scale(1.04);transition:.6s;pointer-events:none;background:#fff}
.sc.on{opacity:1;transform:none}
.sc.zoom{transform:scale(1.5);transition:1.2s}
.cap{position:absolute;left:14px;right:14px;bottom:16px;background:#fff;color:var(--ink);border-radius:14px;padding:10px 14px;font-size:13px;font-weight:600;text-align:center;box-shadow:var(--sh);z-index:30;transition:.4s}
.top{background:linear-gradient(135deg,var(--g),var(--g2));color:#fff;padding:34px 18px 18px}
.card{background:#fff;border-radius:16px;box-shadow:var(--sh);padding:14px}
.btn{display:block;border-radius:12px;padding:12px;text-align:center;font-weight:800;font-size:14px;color:#fff;background:var(--g);box-shadow:var(--sh);transition:.2s}
.btn.b{background:var(--bl)}
.press{transform:scale(.94);filter:brightness(.9)}
#fg{position:absolute;width:38px;height:38px;border-radius:50%;background:rgba(18,48,31,.35);border:3px solid #fff;z-index:50;transition:left .8s cubic-bezier(.5,0,.2,1),top .8s cubic-bezier(.5,0,.2,1),opacity .3s;left:180px;top:700px;box-shadow:0 4px 12px rgba(0,0,0,.3);margin:-19px 0 0 -19px}
#fg.tap::after{content:"";position:absolute;inset:-6px;border-radius:50%;border:3px solid var(--g);animation:rp .5s forwards}
@keyframes rp{to{transform:scale(2.4);opacity:0}}
input,.fld{width:100%;border:2px solid #d3e4d9;border-radius:10px;padding:11px;font:600 14px inherit;margin-top:6px;background:#f8fcf9;min-height:42px;color:var(--ink)}
.fld.f{border-color:var(--g)}
.chip{display:inline-block;padding:3px 10px;border-radius:99px;font-size:11px;font-weight:800;color:#fff;transition:.5s}
.day{display:inline-block;width:14.1%;text-align:center;padding:7px 0;font-size:12px;font-weight:600;border-radius:50%}
.day.s{background:var(--g);color:#fff}
.dots i{display:inline-block;width:9px;height:9px;border-radius:50%;background:var(--g);margin:3px;animation:bn 1s infinite}
.dots i:nth-child(2){animation-delay:.15s}.dots i:nth-child(3){animation-delay:.3s}
@keyframes bn{50%{transform:translateY(-8px);opacity:.4}}
.pt{display:flex;justify-content:space-between;font-size:10px;text-align:center;font-weight:600}
.pt b{display:block;font-size:12px}
#ctl{display:flex;gap:8px;align-items:center;font-size:13px}
#ctl button{border:0;background:var(--g);color:#fff;font:700 13px inherit;border-radius:99px;padding:8px 16px;cursor:pointer}
#pb{width:140px;height:5px;background:rgba(31,170,89,.25);border-radius:9px;overflow:hidden}#pb i{display:block;height:100%;background:var(--g);width:0}
.ok{position:absolute;inset:0;background:rgba(255,255,255,.96);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:12px;opacity:0;transition:.4s;font-weight:800;z-index:20}
.ok.on{opacity:1}.ok svg{transform:scale(0);transition:.5s .15s cubic-bezier(.3,1.6,.5,1)}.ok.on svg{transform:scale(1)}
</style></head><body>
<div id="holder"><div id="stage">

<svg width="0" height="0" style="position:absolute"><symbol id="lg" viewBox="0 0 64 64"><rect width="64" height="64" rx="15" fill="#1faa59"/><path d="M32 22c-5-4-12-4-18-2v26c6-2 13-2 18 2 5-4 12-4 18-2V20c-6-2-13-2-18 2z" fill="#fff"/><path d="M32 22v26" stroke="#1faa59" stroke-width="2.5"/></symbol></svg>

<!-- 1 -->
<div class="sc" id="s1" style="background:linear-gradient(160deg,#a8e6c1,#e9f9ef)">
 <div style="text-align:center;padding-top:60px"><div style="font-size:58px;font-weight:800">09:41</div><div style="font-size:13px">Rabu, 7 Oktober</div></div>
 <div class="card" id="nt" style="position:absolute;left:16px;right:16px;top:190px;display:flex;gap:10px;align-items:center;transform:translateY(-30px);opacity:0;transition:.6s"><div style="width:38px;height:38px;border-radius:10px;background:#25d366;color:#fff;display:grid;place-items:center;font-weight:800">W</div><div style="font-size:12px"><b>Qlab</b><br>Install aplikasi Breskul di sini…</div></div>
 <div id="chat" style="position:absolute;inset:0;background:#efeae2;transform:translateY(100%);transition:.6s">
  <div style="background:#128c7e;color:#fff;padding:34px 16px 12px;font-weight:800">Qlab</div>
  <div class="card" style="margin:18px 14px;font-size:12px;line-height:1.5;border-radius:4px 14px 14px 14px"><b>Info Instalasi Breskul</b><br>Aplikasi absensi &amp; LMS sekolah. Unduh lewat tautan:<br><span id="lk" style="color:#1366d6;font-weight:700;text-decoration:underline">play.google.com/store/apps/breskul</span></div></div>
</div>

<!-- 2 -->
<div class="sc" id="s2">
 <div style="padding:40px 20px"><div style="display:flex;gap:14px;align-items:center"><svg width="80" height="80"><use href="#lg"/></svg><div><b style="font-size:20px">Breskul</b><div style="font-size:12px;color:#5b7a69">Learning Management System</div></div></div>
 <div class="btn" id="pl" style="margin-top:26px">Install</div>
 <div style="display:flex;justify-content:space-around;margin-top:24px;font-size:12px;text-align:center"><div><b>4,8★</b><br>Rating</div><div><b>10 rb+</b><br>Unduhan</div><div><b>3+</b><br>Usia</div></div>
 <div style="height:160px;margin-top:22px;border-radius:16px;background:linear-gradient(135deg,var(--gl),#c6ecd5)"></div></div>
 <div id="spl" style="position:absolute;inset:0;background:linear-gradient(160deg,#fff,var(--gl));display:flex;flex-direction:column;align-items:center;justify-content:center;gap:16px;opacity:0;transition:.5s"><svg width="110" height="110" style="filter:drop-shadow(0 8px 14px rgba(14,90,50,.3))"><use href="#lg"/></svg><b style="font-size:24px;color:var(--g2)">Breskul</b><div class="dots"><i></i><i></i><i></i></div></div>
</div>

<!-- 3 -->
<div class="sc" id="s3" style="background:linear-gradient(180deg,var(--gl),#fff)">
 <div style="text-align:center;padding:60px 24px 20px"><svg width="76" height="76"><use href="#lg"/></svg><div style="font-weight:800;font-size:18px;margin-top:10px">Breskul - Learning Management System</div></div>
 <div class="card" style="margin:0 22px;padding:20px"><label style="font-size:12px;font-weight:700">Username</label><div class="fld" id="u"></div><label style="font-size:12px;font-weight:700;display:block;margin-top:12px">Password</label><div class="fld" id="p"></div><div class="btn" id="lb" style="margin-top:20px">MASUK</div></div>
</div>

<!-- 4 -->
<div class="sc" id="s4"></div>

<!-- 5 -->
<div class="sc" id="s5">
 <div class="top"><b>Absensi</b></div>
 <div class="card" style="margin:16px"><div style="font-size:12px;font-weight:700;color:var(--g2)">STATUS LOKASI</div><div style="margin:8px 0;background:var(--gl);border-radius:12px;padding:12px;font-size:13px;font-weight:800;color:var(--g2);text-align:center">📍 DALAM RADIUS ABSENSI<br><span style="font-weight:600">5.6 m dari maks. 10 m</span></div>
 <div style="height:90px;border-radius:12px;background:radial-gradient(circle at 50% 50%,#9be0b5 0 10px,transparent 11px),repeating-linear-gradient(0deg,#e5f1e9 0 1px,transparent 1px 22px),repeating-linear-gradient(90deg,#e5f1e9 0 1px,transparent 1px 22px)"></div>
 <div class="btn" id="cb" style="margin-top:14px">Buka Kamera Absensi Wajah</div></div>
 <div id="cam" style="position:absolute;inset:0;background:#10221a;opacity:0;transition:.5s;z-index:10">
  <div style="color:#fff;text-align:center;padding-top:44px;font-weight:700" id="ct">Posisikan wajah dalam oval</div>
  <div id="ov" style="position:absolute;left:80px;top:130px;width:200px;height:260px;border-radius:50%;border:4px dashed #fff;transition:.5s"></div>
  <svg id="fc" viewBox="0 0 100 130" style="position:absolute;left:90px;top:140px;width:180px;height:240px;opacity:0;transition:.8s;transform:translateY(30px)"><ellipse cx="50" cy="70" rx="34" ry="42" fill="#f2c9a5"/><path d="M14 62c-2-34 20-42 38-40 22 0 36 14 34 40-6-16-18-22-34-20-14 0-30 2-38 20z" fill="#3a2a22"/><circle cx="37" cy="68" r="4" fill="#3a2a22"/><circle cx="63" cy="68" r="4" fill="#3a2a22"/><path d="M40 92q10 8 20 0" stroke="#b0604a" stroke-width="3" fill="none" stroke-linecap="round"/></svg>
  <div style="position:absolute;left:0;right:0;top:410px;text-align:center;color:#7dffb0;font-weight:800;opacity:0;transition:.4s" id="det">✔ Wajah terdeteksi</div>
  <div style="position:absolute;left:30px;right:30px;top:490px;display:flex;gap:12px"><div class="btn" id="bm" style="flex:1">MASUK</div><div class="btn b" id="bp" style="flex:1;background:#e08a1e">PULANG</div></div>
  <div class="ok" id="ok"><svg width="100" height="100" viewBox="0 0 100 100"><circle cx="50" cy="50" r="46" fill="#1faa59"/><path d="M28 52l16 16 30-34" stroke="#fff" stroke-width="9" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg><div id="okt" style="color:var(--g2);font-size:18px"></div></div>
 </div>
 <div class="ok" id="gap" style="background:#fff;z-index:25;font-size:16px;color:var(--g2)">⏱ Beberapa jam kemudian…</div>
</div>

<!-- 6 -->
<div class="sc" id="s6"></div>

<!-- 7 -->
<div class="sc" id="s7" style="background:linear-gradient(160deg,#c4f0d5,#fff 70%);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:18px;text-align:center">
 <svg width="120" height="120" style="filter:drop-shadow(0 10px 18px rgba(14,90,50,.3))"><use href="#lg"/></svg>
 <b style="font-size:30px;color:var(--g2)">Breskul</b>
 <div style="font-size:20px;font-weight:800;padding:0 30px">Absensi Online Lebih Mudah</div>
</div>

<div class="cap" id="cap"></div>
<div id="fg"></div>
</div></div>
<div id="ctl"><button id="re">▶ Ulangi</button><button id="pa">⏸ Jeda</button><div id="pb"><i></i></div></div>

<script>
const $=s=>document.querySelector(s),st=$('#stage'),fg=$('#fg'),cap=$('#cap');
let tok=0,paused=false;
const sleep=ms=>new Promise(r=>{let t=0;const i=setInterval(()=>{if(!paused)t+=40;if(t>=ms){clearInterval(i);r()}},40)});
function fit(){const s=Math.min(innerWidth/360,(innerHeight-60)/640,1.4);st.style.transform=`scale(${s})`;const h=$('#holder');h.style.width=360*s+'px';h.style.height=640*s+'px';window.S=s}
addEventListener('resize',fit);fit();
const dash=(a,b)=>`
<div class="top"><div style="font-size:11px;opacity:.85">BINA INSAN TAQWA</div><div style="font-size:19px;font-weight:800;margin:4px 0 12px">Assalamualaikum TEST AKUN</div>
<div class="pt"><span>Subuh<b>04:32</b></span><span>Zuhur<b>11:42</b></span><span>Ashar<b>14:56</b></span><span>Maghrib<b>17:48</b></span><span>Isya<b>18:57</b></span></div></div>
<div class="card" style="margin:-14px 16px 0"><b style="font-size:13px">Status Absensi Hari Ini</b>
<div style="display:flex;gap:10px;margin-top:10px;text-align:center;font-size:12px;font-weight:800">
<div style="flex:1;background:${a?'#e8f1ff':'#f3f5f4'};border-radius:12px;padding:12px;color:${a?'var(--bl)':'#7a8a82'}">MASUK<br><span id="${a?'ma':''}">${a}</span></div>
<div style="flex:1;background:${b.includes('SUDAH')?'#e8f1ff':'#f3f5f4'};border-radius:12px;padding:12px;color:${b.includes('SUDAH')?'var(--bl)':'#7a8a82'}">PULANG<br>${b}</div></div></div>
<div class="btn b" id="${a&&b.includes('SUDAH')?'iz':'iz0'}" style="margin:16px">Ajukan Izin</div>
<div style="display:flex;gap:10px;margin:0 16px"><div class="card" style="flex:1;text-align:center;font-size:12px;font-weight:700">📚 Jadwal</div><div class="card" style="flex:1;text-align:center;font-size:12px;font-weight:700">📝 Tugas</div></div>`;
$('#s4').innerHTML=dash('✔ SUDAH','BELUM');
let cal='';for(let i=0;i<3;i++)cal+='<span class="day"></span>';for(let d=1;d<=31;d++)cal+=`<span class="day" id="d${d}">${d}</span>`;
$('#s6').innerHTML=`<div id="h6">${dash('✔ SUDAH','✔ SUDAH')}</div>
<div id="sh" style="position:absolute;left:0;right:0;bottom:0;top:90px;background:#fff;border-radius:24px 24px 0 0;box-shadow:0 -8px 30px rgba(0,0,0,.2);transform:translateY(110%);transition:.6s;padding:18px;z-index:5">
<b>Ajukan Izin</b><div style="font-size:12px;margin:8px 0 4px;font-weight:700">Oktober 2026</div><div style="font-size:11px;color:#7a8a82;display:flex;justify-content:space-between;padding:0 6px"><span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span><span>Min</span></div><div>${cal}</div>
<div class="fld" id="rs" style="margin-top:12px;color:#9aa;"><span id="rt">Alasan izin</span></div><div class="btn" id="aj" style="margin-top:14px">Ajukan</div>
<div class="card" id="rw" style="margin-top:14px;display:none;font-size:12px;justify-content:space-between;align-items:center"><span><b>14 Okt 2026</b><br>keluar kota</span><span class="chip" id="chp" style="background:var(--or)">Pending</span></div></div>`;
async function go(el,tap=true){const sr=st.getBoundingClientRect(),r=el.getBoundingClientRect();fg.style.opacity=1;fg.style.left=(r.left-sr.left+r.width/2)/S+'px';fg.style.top=(r.top-sr.top+r.height*.6)/S+'px';await sleep(900);if(tap){fg.classList.add('tap');el.classList.add('press');await sleep(260);el.classList.remove('press');fg.classList.remove('tap');await sleep(250)}}
async function type(el,t,w){el.classList.add('f');el.textContent='';for(const c of t){el.textContent+=w?'•':c;await sleep(90)}el.classList.remove('f')}
async function show(n,c){document.querySelectorAll('.sc').forEach(s=>s.classList.remove('on','zoom'));$('#s'+n).classList.add('on');cap.textContent=c;fg.style.opacity=0;fg.style.top='700px';await sleep(700)}
async function face(label,btn){const cam=$('#cam');cam.style.opacity=1;$('#ct').textContent='Posisikan wajah dalam oval';await sleep(900);$('#fc').style.cssText+=';opacity:1;transform:none';await sleep(900);$('#ov').style.cssText+=';border:4px solid #3dff8a;box-shadow:0 0 30px #3dff8a88';$('#det').style.opacity=1;cap.textContent='Wajah berada di dalam lingkaran hijau';await go(btn);$('#okt').textContent=label+' berhasil';$('#ok').classList.add('on');cap.textContent='Absensi '+label.toLowerCase()+' tercatat ✔';await sleep(1700);$('#ok').classList.remove('on');cam.style.opacity=0;$('#fc').style.opacity=0;$('#det').style.opacity=0;$('#ov').style.cssText='left:80px;top:130px;width:200px;height:260px;border-radius:50%;border:4px dashed #fff;transition:.5s';await sleep(600)}
async function run(){const my=++tok,bar=$('#pb i');bar.style.transition='none';bar.style.width='0';await sleep(50);bar.style.transition='width 62s linear';bar.style.width='100%';
 const A=async f=>{await f();if(my!==tok)throw 0};
 try{
 // reset
 $('#nt').style.cssText+=';transform:translateY(-30px);opacity:0';$('#chat').style.transform='translateY(100%)';$('#spl').style.opacity=0;$('#pl').textContent='Install';$('#u').textContent='';$('#p').textContent='';$('#sh').style.transform='translateY(110%)';$('#rw').style.display='none';$('#rs').style.color='#9aa';$('#rt').textContent='Alasan izin';document.querySelectorAll('.day').forEach(d=>d.classList.remove('s'));$('#chp').textContent='Pending';$('#chp').style.background='var(--or)';
 await A(()=>show(1,'Buka notifikasi WhatsApp dari Qlab'));
 $('#nt').style.cssText+=';transform:none;opacity:1';await A(()=>sleep(1200));
 await A(()=>go($('#nt')));$('#chat').style.transform='none';cap.textContent='Pesan berisi info instalasi & tautan unduhan';await A(()=>sleep(1500));await A(()=>go($('#lk')));
 await A(()=>show(2,'Halaman Google Play: Breskul'));await A(()=>sleep(700));$('#pl').textContent='Menginstal…';await A(()=>sleep(1300));$('#pl').textContent='Buka';cap.textContent='Setelah terpasang, ketuk "Buka"';await A(()=>go($('#pl')));$('#spl').style.opacity=1;await A(()=>sleep(2000));
 await A(()=>show(3,'Masuk dengan username dan password'));await A(()=>sleep(500));await A(()=>go($('#u'),false));await A(()=>type($('#u'),'testakun'));await A(()=>go($('#p'),false));await A(()=>type($('#p'),'12345678',1));await A(()=>go($('#lb')));
 await A(()=>show(4,'Beranda: status absensi & waktu salat'));await A(()=>sleep(3600));
 await A(()=>show(5,'Buka tab Absensi: pastikan dalam radius'));await A(()=>sleep(1800));await A(()=>go($('#cb')));
 await A(()=>face('MASUK',$('#bm')));
 $('#gap').classList.add('on');cap.textContent='Pulang sekolah';await A(()=>sleep(1500));$('#gap').classList.remove('on');await A(()=>sleep(500));await A(()=>go($('#cb')));
 await A(()=>face('PULANG',$('#bp')));
 await A(()=>show(6,'Masuk & Pulang kini berstatus SUDAH'));await A(()=>sleep(1800));await A(()=>go($('#iz')));$('#sh').style.transform='none';cap.textContent='Pilih tanggal izin di kalender';await A(()=>sleep(900));await A(()=>go($('#d14')));$('#d14').classList.add('s');
 cap.textContent='Tulis alasan izin';await A(()=>go($('#rs'),false));$('#rt').textContent='';$('#rs').style.color='var(--ink)';for(const c of 'keluar kota'){$('#rt').textContent+=c;await A(()=>sleep(90))}
 await A(()=>go($('#aj')));$('#rw').style.display='flex';cap.textContent='Pengajuan terkirim: status Pending';fg.style.opacity=0;await A(()=>sleep(2000));$('#chp').textContent='Diterima';$('#chp').style.background='var(--g)';cap.textContent='Izin disetujui: status Diterima ✔';await A(()=>sleep(2400));
 await A(()=>show(7,'Selesai! Selamat mencoba'));cap.style.opacity=0;await A(()=>sleep(3500));cap.style.opacity=1;
 }catch(e){}}
$('#re').onclick=()=>{paused=false;$('#pa').textContent='⏸ Jeda';cap.style.opacity=1;run()};
$('#pa').onclick=()=>{paused=!paused;$('#pa').textContent=paused?'▶ Lanjut':'⏸ Jeda'};
run();
</script></body></html>
