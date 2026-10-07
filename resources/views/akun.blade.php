<!DOCTYPE html>
<html lang="id"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Tutorial Lupa Password Breskul</title>
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
<div class="sc" id="s1" style="background:linear-gradient(180deg,var(--gl),#fff)">
 <div style="text-align:center;padding:60px 24px 20px"><svg width="76" height="76"><use href="#lg"/></svg><div style="font-weight:800;font-size:18px;margin-top:10px">Breskul - Learning Management System</div></div>
 <div class="card" style="margin:0 22px;padding:20px"><label style="font-size:12px;font-weight:700">Username</label><div class="fld" id="u"></div><label style="font-size:12px;font-weight:700;display:block;margin-top:12px">Password</label><div class="fld" id="p"></div><div style="text-align:right;margin-top:10px"><span id="lp" style="font-size:13px;font-weight:800;color:var(--bl);padding:4px">Lupa password?</span></div><div class="btn" id="lb" style="margin-top:10px">MASUK</div></div>
</div>
<!-- 2 -->
<div class="sc" id="s2" style="background:linear-gradient(180deg,var(--gl),#fff)">
 <div style="text-align:center;padding:70px 24px 20px"><div style="font-size:46px">🔑</div><div style="font-weight:800;font-size:20px;margin-top:8px">Lupa Password</div><div style="font-size:12px;color:#5b7a69;margin-top:6px">Masukkan nomor WhatsApp yang terdaftar di sekolah</div></div>
 <div class="card" style="margin:0 22px;padding:20px"><label style="font-size:12px;font-weight:700">Nomor WhatsApp</label><div class="fld" id="wa"></div><div class="btn" id="kb" style="margin-top:20px">Kirim Akses Akun</div></div>
 <div class="ok" id="ok"><svg width="100" height="100" viewBox="0 0 100 100"><circle cx="50" cy="50" r="46" fill="#25d366"/><path d="M28 52l16 16 30-34" stroke="#fff" stroke-width="9" fill="none" stroke-linecap="round" stroke-linejoin="round"/></svg><div style="color:var(--g2);font-size:17px;text-align:center;padding:0 30px">Akses akun dikirim<br>ke WhatsApp Anda</div></div>
</div>
<!-- 3 -->
<div class="sc" id="s3" style="background:linear-gradient(160deg,#a8e6c1,#e9f9ef)">
 <div style="text-align:center;padding-top:60px"><div style="font-size:58px;font-weight:800">09:43</div><div style="font-size:13px">Rabu, 7 Oktober</div></div>
 <div class="card" id="nt" style="position:absolute;left:16px;right:16px;top:190px;display:flex;gap:10px;align-items:center;transform:translateY(-30px);opacity:0;transition:.6s"><div style="width:38px;height:38px;border-radius:10px;background:#25d366;color:#fff;display:grid;place-items:center;font-weight:800">W</div><div style="font-size:12px"><b>Breskul</b><br>Akses akun Anda sudah siap…</div></div>
 <div id="chat" style="position:absolute;inset:0;background:#efeae2;transform:translateY(100%);transition:.6s">
  <div style="background:#128c7e;color:#fff;padding:34px 16px 12px;font-weight:800">Breskul</div>
  <div class="card" style="margin:18px 14px;font-size:13px;line-height:1.7;border-radius:4px 14px 14px 14px"><b>Akses Akun Breskul</b><br>Username: <b>testakun</b><br>Password: <b id="pw">••••••••</b><br><span style="font-size:11px;color:#5b7a69">Segera ganti password setelah login. Jangan bagikan ke siapa pun.</span></div></div>
</div>
<!-- 4 -->
<div class="sc" id="s4" style="background:linear-gradient(160deg,#c4f0d5,#fff 70%);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:18px;text-align:center">
 <svg width="120" height="120" style="filter:drop-shadow(0 10px 18px rgba(14,90,50,.3))"><use href="#lg"/></svg>
 <b style="font-size:30px;color:var(--g2)">Breskul</b>
 <div style="font-size:19px;font-weight:800;padding:0 30px">Lupa password? Akses akun langsung dikirim lewat WhatsApp</div>
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
async function go(el,tap=true){const sr=st.getBoundingClientRect(),r=el.getBoundingClientRect();fg.style.opacity=1;fg.style.left=(r.left-sr.left+r.width/2)/S+'px';fg.style.top=(r.top-sr.top+r.height*.6)/S+'px';await sleep(900);if(tap){fg.classList.add('tap');el.classList.add('press');await sleep(260);el.classList.remove('press');fg.classList.remove('tap');await sleep(250)}}
async function type(el,t,w){el.classList.add('f');el.textContent='';for(const c of t){el.textContent+=w?'•':c;await sleep(90)}el.classList.remove('f')}
async function show(n,c){document.querySelectorAll('.sc').forEach(s=>s.classList.remove('on','zoom'));$('#s'+n).classList.add('on');cap.textContent=c;fg.style.opacity=0;fg.style.top='700px';await sleep(700)}
async function run(){const my=++tok,bar=$('#pb i');bar.style.transition='none';bar.style.width='0';await sleep(50);bar.style.transition='width 34s linear';bar.style.width='100%';
 const A=async f=>{await f();if(my!==tok)throw 0};
 try{
 $('#nt').style.cssText+=';transform:translateY(-30px);opacity:0';$('#chat').style.transform='translateY(100%)';$('#wa').textContent='';$('#ok').classList.remove('on');$('#u').textContent='';$('#p').textContent='';$('#pw').textContent='••••••••';
 await A(()=>show(1,'Lupa username atau password? Ketuk "Lupa password?"'));await A(()=>sleep(1200));await A(()=>go($('#lp')));
 await A(()=>show(2,'Masukkan nomor WhatsApp yang terdaftar'));await A(()=>sleep(500));await A(()=>go($('#wa'),false));await A(()=>type($('#wa'),'0812 3456 7890'));cap.textContent='Lalu ketuk "Kirim Akses Akun"';await A(()=>go($('#kb')));$('#ok').classList.add('on');cap.textContent='Permintaan berhasil dikirim';fg.style.opacity=0;await A(()=>sleep(2200));
 await A(()=>show(3,'Cek WhatsApp: akses akun dikirim otomatis'));$('#nt').style.cssText+=';transform:none;opacity:1';await A(()=>sleep(1300));await A(()=>go($('#nt')));$('#chat').style.transform='none';cap.textContent='Pesan berisi username dan password Anda';await A(()=>sleep(1000));$('#pw').textContent='bk7Q29xa';await A(()=>sleep(2500));
 await A(()=>show(1,'Gunakan akun tersebut untuk login'));await A(()=>go($('#u'),false));await A(()=>type($('#u'),'testakun'));await A(()=>go($('#p'),false));await A(()=>type($('#p'),'bk7Q29xa',1));await A(()=>go($('#lb')));
 await A(()=>show(4,'Selesai!'));cap.style.opacity=0;await A(()=>sleep(3500));cap.style.opacity=1;
 }catch(e){}}
$('#re').onclick=()=>{paused=false;$('#pa').textContent='⏸ Jeda';cap.style.opacity=1;run()};
$('#pa').onclick=()=>{paused=!paused;$('#pa').textContent=paused?'▶ Lanjut':'⏸ Jeda'};
run();
</script></body></html>