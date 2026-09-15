/** Dedicated form and apparatus for bench, support-bar and cardio demonstrations. */
export const FORM_DEMOS = ['calf-raise','cat-cow','chest-fly','chin-up','clap-push-up','close-grip-push-up','cycling','dead-bug','decline-bench-press','dips','dumbbell-pullover','elliptical-trainer','front-raise','hanging-leg-raise','hip-thrust'];
const sides=[-1,1];
const add=(a,b)=>a.map((v,i)=>v+b[i]);
const mix=(a,b,t)=>a.map((v,i)=>v+(b[i]-v)*t);
const length=a=>Math.hypot(...a);
const unit=a=>a.map(v=>v/(length(a)||1));
const dot=(a,b)=>a.reduce((s,v,i)=>s+v*b[i],0);
const tau=Math.PI*2;
function joint(a,b,upper=.30,lower=.28,pole=[0,0,1]) {
 const delta=b.map((v,i)=>v-a[i]),distance=Math.min(length(delta),upper+lower-.0001),axis=unit(delta);
 const along=(upper*upper-lower*lower+distance*distance)/(2*Math.max(.001,distance));
 const bend=unit(pole.map((v,i)=>v-axis[i]*dot(pole,axis)));
 return a.map((v,i)=>v+axis[i]*along+bend[i]*Math.sqrt(Math.max(0,upper*upper-along*along)));
}
function arm(shoulder,wrist,side,pole=[side,-.3,.3]) {return {shoulder,wrist,elbow:joint(shoulder,wrist,.30,.28,pole)};}
function leg(hip,ankle) {return {hip,ankle,knee:joint(hip,ankle,.43,.43,[0,0,1])};}
function upright(pose,y=.98) {
 pose.prone=false;pose.horizontal=false;pose.pelvis=[0,y,0];pose.neck=[0,y+.63,0];pose.head=[0,y+.78,0];
 pose.legs=sides.map(side=>leg(add(pose.pelvis,[side*.12,-.025,0]),[side*.14,.08,.03]));
}
function bench(pose,decline=false) {
 pose.prone=false;pose.horizontal=true;pose.pelvis=[0,decline?.78:.62,.30];pose.neck=[0,decline?.57:.62,-.32];pose.head=[0,decline?.51:.64,-.49];
 pose.legs=sides.map(side=>leg(add(pose.pelvis,[side*.12,0,0]),[side*.20,decline?.20:.08,.82]));
}
function pushPose(pose,amount,clap=0,narrow=false) {
 pose.prone=true;pose.horizontal=false;
 pose.pelvis=[0,.40-.19*amount+.08*clap,.24];pose.neck=[0,.64-.28*amount+.10*clap,-.50];pose.head=[0,.63-.28*amount+.10*clap,-.67];
 pose.legs=sides.map(side=>{const hip=add(pose.pelvis,[side*.12,0,0]),ankle=[side*.13,.08,.98];return {hip,ankle,knee:mix(hip,ankle,.5)};});
 pose.arms=sides.map(side=>arm([side*.23,.59-.28*amount+.10*clap,-.42],[side*(narrow?.09:.26)*(1-.94*clap),.065+.23*clap,-.44],side,[side*.2,0,.5]));
}
export function refineExerciseForm(pose,slug,phase) {
 const t=(1-Math.cos(tau*phase))/2;
 if(!FORM_DEMOS.includes(slug))return pose;
 if(slug==='calf-raise') {
  upright(pose,.94+.10*t);
  pose.legs=sides.map(side=>leg(add(pose.pelvis,[side*.12,-.025,0]),[side*.14,.08+.10*t,.02]));
  pose.arms=sides.map(side=>arm(add(pose.neck,[side*.23,-.12,0]),side===1?[.44,1.15,.22]:[-.28,.94+.10*t,.04],side));
 } else if(slug==='cat-cow') {
  const curve=Math.cos(phase*tau);pose.prone=true;pose.pelvis=[0,.53,.28];pose.neck=[0,.54,-.34];pose.head=[0,.56-.10*curve,-.51];pose.spineArch=.085*curve;
  pose.legs=sides.map(side=>({hip:add(pose.pelvis,[side*.12,0,0]),knee:[side*.13,.10,.28],ankle:[side*.13,.085,.67]}));
  pose.arms=sides.map(side=>({shoulder:[side*.22,.52,-.34],elbow:[side*.22,.30,-.34],wrist:[side*.22,.065,-.34]}));
 } else if(['chest-fly','decline-bench-press','dumbbell-pullover'].includes(slug)) {
  bench(pose,slug==='decline-bench-press');
  pose.arms=sides.map(side=>{const shoulder=add(pose.neck,[side*.23,.025,.09]);let wrist;
   if(slug==='chest-fly')wrist=[side*(.065+.59*t),shoulder[1]+.52*(1-t),shoulder[2]];
   else if(slug==='decline-bench-press')wrist=[side*.30,.80+.35*(1-t),-.20];
   else wrist=[side*.055,shoulder[1]+.52*Math.cos(t*Math.PI/2),shoulder[2]-.52*Math.sin(t*Math.PI/2)];
   return arm(shoulder,wrist,side,[side,-.7,.1]);
  });
 } else if(slug==='chin-up'||slug==='hanging-leg-raise') {
  const lift=slug==='chin-up'?.45*t:0;
  upright(pose,1.06+lift);
  pose.legs=sides.map(side=>leg(add(pose.pelvis,[side*.12,-.025,0]),[side*.14,.20+lift,.03]));
  pose.arms=sides.map(side=>arm(add(pose.neck,[side*.23,-.12,0]),[side*.25,2.15,0],side,[side,-.5,.25]));
  if(slug==='hanging-leg-raise')pose.legs=sides.map(side=>{const hip=add(pose.pelvis,[side*.12,0,0]),angle=1.45*t,knee=add(hip,[0,-.43*Math.cos(angle),.43*Math.sin(angle)]);return {hip,knee,ankle:add(knee,[0,-.43*Math.cos(angle),.43*Math.sin(angle)])};});
 } else if(slug==='dips') {
  upright(pose,1.10-.26*t);pose.neck[2]=.10;pose.head[2]=.12;
  pose.arms=sides.map(side=>arm(add(pose.neck,[side*.23,-.12,0]),[side*.33,1.20,.05],side,[side,0,-1]));
  pose.legs=sides.map(side=>{const hip=add(pose.pelvis,[side*.12,0,0]),knee=add(hip,[0,-.36,.03]);return {hip,knee,ankle:add(knee,[0,-.28,-.30])};});
 } else if(slug==='front-raise') {
  upright(pose);const angle=t*Math.PI/2;
  pose.arms=sides.map(side=>{const shoulder=add(pose.neck,[side*.23,-.12,0]),elbow=add(shoulder,[0,-.30*Math.cos(angle),.30*Math.sin(angle)]);return {shoulder,elbow,wrist:add(elbow,[0,-.28*Math.cos(angle),.28*Math.sin(angle)])};});
 } else if(slug==='dead-bug') {
  const active=phase%1<.5?0:1,extend=Math.sin(tau*phase)**2;
  pose.horizontal=true;pose.prone=false;pose.pelvis=[0,.16,.15];pose.neck=[0,.18,-.47];pose.head=[0,.20,-.65];
  pose.legs=sides.map((side,i)=>{const hip=add(pose.pelvis,[side*.12,0,0]),ankle=mix([side*.12,.56,.66],[side*.12,.11,.96],i===active?extend:0);return {hip,ankle,knee:joint(hip,ankle,.43,.43,[0,1,0])};});
  pose.arms=sides.map((side,i)=>{const shoulder=add(pose.neck,[side*.22,.025,.07]),e=i!==active?extend:0;return {shoulder,elbow:add(shoulder,[0,.28*(1-e)+.02*e,-.28*e]),wrist:add(shoulder,[0,.56*(1-e)+.04*e,-.56*e])};});
 } else if(slug==='close-grip-push-up')pushPose(pose,t,0,true);
 else if(slug==='clap-push-up') {
  const p=((phase%1)+1)%1;
  const frames=[[0,0,0],[.25,1,0],[.43,0,1],[.57,0,0],[1,0,0]];
  const i=frames.findIndex(f=>f[0]>p),a=frames[i-1],b=frames[i],x=(p-a[0])/(b[0]-a[0]),e=x*x*(3-2*x);
  pushPose(pose,a[1]+(b[1]-a[1])*e,a[2]+(b[2]-a[2])*e);
 } else if(slug==='hip-thrust') {
  pose.horizontal=true;pose.prone=false;pose.pelvis=[0,.42+.30*t,.30];pose.neck=[0,.64,-.29];pose.head=[0,.72,-.46];
  pose.legs=sides.map(side=>leg(add(pose.pelvis,[side*.12,0,0]),[side*.17,.08,.73]));
  pose.arms=sides.map(side=>arm([side*.23,.64,-.20],add(pose.pelvis,[side*.25,.12,0]),side));
 } else if(slug==='cycling') {
  upright(pose,.95);pose.neck=[0,1.48,.25];pose.head=[0,1.62,.31];
  pose.legs=sides.map((side,i)=>{const angle=tau*phase+i*Math.PI;return leg(add(pose.pelvis,[side*.12,-.025,0]),[side*.14,.42+.18*Math.cos(angle),.23+.18*Math.sin(angle)]);});
  pose.arms=sides.map(side=>arm(add(pose.neck,[side*.23,-.12,0]),[side*.24,1.25,.70],side,[side,-1,0]));
 } else if(slug==='elliptical-trainer') {
  upright(pose,.93+.01*Math.cos(tau*phase*2));
  pose.legs=sides.map((side,i)=>{const a=tau*phase+i*Math.PI;return leg(add(pose.pelvis,[side*.12,-.025,0]),[side*.15,.18+.06*Math.cos(a),.10+.32*Math.sin(a)]);});
  pose.arms=sides.map((side,i)=>arm(add(pose.neck,[side*.23,-.12,0]),[side*.34,1.22,.55-.16*Math.sin(tau*phase+i*Math.PI)],side,[side,-1,0]));
 }
 return pose;
}
export function formEquipment(pose,slug,phase) {
 const items=[];if(!FORM_DEMOS.includes(slug))return items;
 const line=(points,width=3,role='frame',color='#809fb7')=>items.push({type:'line',points,width,role,color});
 const face=(points,color='#243c50',role='frame')=>items.push({type:'face',points,color,role});
 const ball=(center,radii,color=[77,109,135],role='weight')=>items.push({type:'ellipsoid',center,radii,color,role});
 const support=(y,z,width=.35)=>{line([[-width,.035,z],[width,.035,z]],4);line([[0,.035,z],[0,y,z]],5);};
 const benchPad=(y0,y1,z0,z1,width=.20)=>{
  face([[-width,y0,z0],[width,y0,z0],[width,y1,z1],[-width,y1,z1]],'#354f65','bench');
  face([[-width,y0-.07,z0],[width,y0-.07,z0],[width,y0,z0],[-width,y0,z0]]);
  support(y0-.04,z0+.10,width+.08);support(y1-.04,z1-.10,width+.08);
 };
 const dumbbell=(center,axis='z')=>{const a=axis==='y'?[0,.09,0]:[0,0,.09];line([add(center,a.map(v=>-v)),add(center,a)],3,'grip','#c1d4e5');for(const direction of [-1,1])ball(add(center,a.map(v=>v*direction)),axis==='y'?[.07,.023,.07]:[.065,.065,.025]);};
 const barbell=(center,width=.60)=>{line([add(center,[-width-.10,0,0]),add(center,[width+.10,0,0])],3,'bar','#bfd3e3');for(const side of sides)ball(add(center,[side*width,0,0]),[.035,.13,.13]);};
 if(['cat-cow','dead-bug','clap-push-up','close-grip-push-up'].includes(slug))face([[-.45,-.005,-1.20],[.45,-.005,-1.20],[.45,-.005,1.2],[-.45,-.005,1.2]],'#101e2c','ground');
 if(['chest-fly','decline-bench-press','dumbbell-pullover'].includes(slug)) {
  const decline=slug==='decline-bench-press';benchPad(decline?.43:.49,decline?.69:.49,-.65,.42);
  if(decline){barbell(mix(pose.arms[0].wrist,pose.arms[1].wrist,.5));line([[-.30,.15,.87],[.30,.15,.87]],6,'ankle-support');support(.15,.87);}
  else if(slug==='dumbbell-pullover')dumbbell(mix(pose.arms[0].wrist,pose.arms[1].wrist,.5),'y');
  else pose.arms.forEach(arm=>dumbbell(arm.wrist));
 }
 if(['chin-up','hanging-leg-raise'].includes(slug)) {
  line([[-.65,2.15,0],[.65,2.15,0]],4,'pull-up-bar','#b7d4e9');
  for(const side of sides){line([[side*.65,.03,0],[side*.65,2.15,0]],5);line([[side*.65,.03,-.30],[side*.65,.03,.3]],4);}
 }
 if(slug==='dips')for(const side of sides){line([[side*.33,1.2,-.45],[side*.33,1.2,.45]],5,'dip-bar','#b7d4e9');for(const z of [-.40,.4])line([[side*.33,.03,z],[side*.33,1.2,z]],4);}
 if(slug==='front-raise')pose.arms.forEach(arm=>dumbbell(arm.wrist));
 if(slug==='calf-raise'){face([[-.30,-.005,-.25],[.30,-.005,-.25],[.30,-.005,.35],[-.30,-.005,.35]],'#101e2c','ground');line([[.44,.03,.22],[.44,1.15,.22]],4);line([[.44,1.15,.08],[.44,1.15,.42]],4,'support-rail');}
 if(slug==='hip-thrust'){benchPad(.52,.52,-.44,-.06,.43);barbell(add(pose.pelvis,[0,.12,0]));}
 if(slug==='cycling') {
  const crank=[0,.34,.30];ball([0,.29,.70],[.06,.25,.25],[41,67,87],'flywheel');
  line([[0,.10,-.20],[0,.84,0],crank,[0,.80,.70],[0,.10,.70],[0,.10,-.20]],5);
  support(.12,-.20);support(.12,.70);benchPad(.84,.84,-.12,.14,.14);
  line([[0,.65,.70],[0,1.25,.70]],5);line([[-.25,1.25,.70],[.25,1.25,.70]],4,'handlebar');
  pose.legs.forEach(leg=>{const pedal=add(leg.ankle,[0,-.075,.07]);line([crank,pedal],3,'crank');face([add(pedal,[-.07,0,-.10]),add(pedal,[.07,0,-.10]),add(pedal,[.07,0,.10]),add(pedal,[-.07,0,.10])],'#4c6a80','pedal');});
 }
 if(slug==='elliptical-trainer') {
  ball([0,.28,.80],[.10,.23,.23],[41,67,87],'flywheel');
  support(.15,.80);support(.10,-.4);line([[0,.10,-.45],[0,.12,.85]],5);
  pose.legs.forEach((leg,i)=>{const p=add(leg.ankle,[0,-.075,.07]);face([add(p,[-.09,0,-.17]),add(p,[.09,0,-.17]),add(p,[.09,0,.17]),add(p,[-.09,0,.17])],'#4c6a80','pedal');line([[sides[i]*.15,.28,.80],p],4);});
  pose.arms.forEach(arm=>{const pivot=[arm.wrist[0],.25,.70],top=add(pivot,arm.wrist.map((v,i)=>(v-pivot[i])*1.30/.97));line([pivot,top],4,'moving-handle');});
 }
 return items;
}
