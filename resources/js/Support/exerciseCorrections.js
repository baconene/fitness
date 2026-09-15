/** Authored movements and equipment for exercises that cannot share a generic cycle. */
export const CABLE_DEMOS = ['cable-crossover', 'seated-cable-row', 'face-pull', 'straight-arm-pulldown', 'cable-curl', 'triceps-pushdown', 'pallof-press', 'cable-woodchop', 'lat-pulldown'];
export const CORRECTED_DEMOS = ['ab-wheel-rollout', 'battle-ropes', 'bicycle-crunch', 'bird-dog', 'box-jump', 'broad-jump', 'burpee', ...CABLE_DEMOS];
const add = (a,b) => a.map((v,i)=>v+b[i]);
const mix = (a,b,t) => a.map((v,i)=>v+(b[i]-v)*t);
const sides = [-1,1];
const ease = t => t*t*(3-2*t);

function knee(hip, ankle) {
    const delta = ankle.map((v,i)=>v-hip[i]);
    const distance = Math.hypot(...delta);
    const along = delta.map(v=>v/(distance||1));
    const pole = [0,-along[2],along[1]];
    if (pole[2]<0) pole.forEach((v,i)=>pole[i]=-v);
    const length = Math.hypot(...pole)||1;
    const bend = Math.sqrt(Math.max(0,.43**2-(distance/2)**2));
    return mix(hip,ankle,.5).map((v,i)=>v+pole[i]/length*bend);
}

function standing(z=0, height=0, crouch=0, armsUp=0) {
    const pelvis=[0,.98+height-.30*crouch,z-.12*crouch];
    const neck=add(pelvis,[0,.63-.05*crouch,.12*crouch]);
    return {
        pelvis,neck,head:add(neck,[0,.15,0]),
        arms:sides.map(side=>{const shoulder=add(neck,[side*.23,-.12,0]);
            const elbow=add(shoulder,[side*.025,-.28+.48*armsUp,-.08+.15*crouch]);
            return {shoulder,elbow,wrist:add(elbow,[0,-.25+.50*armsUp,.09])};}),
        legs:sides.map(side=>{const hip=add(pelvis,[side*.12,-.025,0]);const ankle=[side*.15,.08+height,z];return {hip,ankle,knee:knee(hip,ankle)};}),
    };
}

function interpolatePose(a,b,t) {
    const result={};
    for(const name of ['pelvis','neck','head']) result[name]=mix(a[name],b[name],t);
    for(const group of ['arms','legs']) result[group]=a[group].map((limb,i)=>Object.fromEntries(Object.keys(limb).map(key=>[key,mix(limb[key],b[group][i][key],t)])));
    return result;
}

function keyframes(frames,phase) {
    const p=((phase%1)+1)%1;
    const index=frames.findIndex(([time])=>time>p);
    const [start,a]=frames[Math.max(0,index-1)], [end,b]=frames[index];
    return interpolatePose(a,b,ease((p-start)/(end-start)));
}

function burpeeFloor(lowered=false) {
    const pelvis=[0,lowered?.22:.34,-.20],neck=[0,lowered?.22:.49,.42];
    return {pelvis,neck,head:add(neck,[0,0,.17]),
        arms:sides.map(side=>({shoulder:add(neck,[side*.24,-.03,0]),elbow:[side*(lowered?.35:.25),lowered?.14:.28,.28],wrist:[side*.25,.065,.43]})),
        legs:sides.map(side=>({hip:add(pelvis,[side*.12,0,0]),knee:[side*.13,.18,-.59],ankle:[side*.13,.08,-.99]})),
    };
}

export function correctExercisePose(pose,slug,phase) {
    const t=(1-Math.cos(phase*Math.PI*2))/2;
    if(slug==='box-jump'||slug==='broad-jump') {
        const box=slug==='box-jump',distance=box?.75:1.05,height=box?.42:0;
        const start=standing(),load=standing(0,0,.85),land=standing(distance,height,.55),top=standing(distance,height);
        const step=standing(distance*.48,height*.5,box?1:.5);
        step.legs[0].ankle=[-.15,.08,0];step.legs[1].ankle=[.15,height+.08,distance];
        step.legs.forEach(leg=>leg.knee=knee(leg.hip,leg.ankle));
        Object.assign(pose,keyframes([[0,start],[.15,load],[.29,standing(distance*.48,box?.61:.40,.20,1)],[.43,land],[.55,top],[.76,step],[.94,start],[1,start]],phase));
        pose.prone=false;pose.horizontal=false;
    } else if(slug==='burpee') {
        const start=standing(),squat=standing(0,0,1.30);
        squat.arms=sides.map(side=>({shoulder:add(squat.neck,[side*.23,-.12,0]),elbow:[side*.24,.36,.24],wrist:[side*.25,.065,.43]}));
        Object.assign(pose,keyframes([[0,start],[.14,squat],[.27,burpeeFloor()],[.39,burpeeFloor(true)],[.50,burpeeFloor()],[.64,squat],[.78,standing(0,.35,0,1)],[.9,standing(0,0,.45)],[1,start]],phase));
        pose.prone=false;pose.horizontal=false;
    } else if(slug==='bird-dog') {
        const extend=Math.sin(phase*Math.PI*2)**2,active=phase%1<.5?0:1;
        pose.pelvis=[0,.53,.28];pose.neck=[0,.56,-.34];pose.head=[0,.55,-.50];pose.prone=true;
        pose.legs=sides.map((side,index)=>{const hip=add(pose.pelvis,[side*.12,0,0]);const amount=index===active?extend:0;
            return {hip,knee:mix([side*.13,.10,.28],add(hip,[0,0,.41]),amount),ankle:mix([side*.13,.085,.66],add(hip,[0,0,.82]),amount)};});
        pose.arms=sides.map((side,index)=>{const shoulder=add(pose.neck,[side*.22,-.02,0]);const amount=index!==active?extend:0;
            return {shoulder,elbow:mix([side*.22,.30,-.34],add(shoulder,[0,0,-.28]),amount),wrist:mix([side*.22,.07,-.34],add(shoulder,[0,0,-.56]),amount)};});
    } else if(slug==='bicycle-crunch') {
        const wave=Math.cos(phase*Math.PI*2);
        pose.pelvis=[0,.16,.14];pose.neck=[.025*wave,.30,-.42];pose.head=[.03*wave,.39,-.56];pose.horizontal=true;
        pose.legs=sides.map((side,index)=>{const drive=(1+(index?wave:-wave))/2,angle=.15+1.95*drive;
            const hip=add(pose.pelvis,[side*.12,0,0]);const knee=add(hip,[0,.43*Math.sin(angle),.43*Math.cos(angle)]);
            return {hip,knee,ankle:add(knee,[0,.43*Math.sin(-.10-.60*drive),.43*Math.cos(-.10-.60*drive)])};});
        pose.arms=sides.map((side,index)=>{const drive=(1+(index?-wave:wave))/2;
            const shoulder=add(pose.neck,[side*.22,.025+.05*drive,.025+.08*drive]);
            return {shoulder,elbow:[side*.27,.39,-.23+.18*drive],wrist:add(pose.head,[side*.085,.015,.035])};});
    } else if(slug==='battle-ropes') {
        Object.assign(pose,standing(0,0,.30));
        pose.arms=sides.map((side,index)=>{const shoulder=add(pose.neck,[side*.23,-.12,0]);
            const wrist=[side*.28,.82+.16*Math.sin(phase*Math.PI*4+index*Math.PI),.34];
            return {shoulder,elbow:mix(shoulder,wrist,.55).map((v,i)=>v+(i===0?side*.05:0)),wrist};});
    } else if(slug==='ab-wheel-rollout') {
        pose.arms.forEach(arm=>{arm.wrist[0]=Math.sign(arm.wrist[0])*.13;arm.wrist[1]=.16;arm.elbow=mix(arm.shoulder,arm.wrist,.52);});
    } else if(CABLE_DEMOS.includes(slug)&&slug!=='lat-pulldown') {
        Object.assign(pose,standing());pose.prone=false;pose.horizontal=false;
        if(slug==='seated-cable-row') {
            pose.pelvis=[0,.59,0];pose.neck=[0,1.18,-.06];pose.head=[0,1.33,-.06];
            pose.legs=sides.map(side=>{const hip=add(pose.pelvis,[side*.12,0,0]),ankle=[side*.16,.10,.65];return {hip,ankle,knee:knee(hip,ankle)};});
        }
        pose.arms=sides.map(side=>{
            const shoulder=add(pose.neck,[side*.23,-.12,0]);let wrist,elbow;
            if(slug==='cable-crossover') {wrist=[side*(.53-.45*t),1.25-.12*t,.14+.40*t];elbow=mix(shoulder,wrist,.52);elbow[1]-=.07;}
            if(slug==='seated-cable-row') {wrist=[side*.10,.88,.56-.38*t];elbow=mix(shoulder,wrist,.5);elbow[0]=side*.29;elbow[2]-=.12*t;}
            if(slug==='face-pull') {wrist=[side*(.12+.13*t),1.45,.58-.44*t];elbow=[side*(.27+.18*t),1.37,.30-.28*t];}
            if(slug==='straight-arm-pulldown') {wrist=[side*.22,1.73-.77*t,.46-.23*t];elbow=mix(shoulder,wrist,.52);}
            if(slug==='cable-curl'||slug==='triceps-pushdown') {
                elbow=[side*.23,1.17,.07];const angle=slug==='cable-curl'?.10+2.15*t:2.0-1.9*t;
                wrist=add(elbow,[0,-.28*Math.cos(angle),.28*Math.sin(angle)]);
            }
            if(slug==='pallof-press') {wrist=[side*.04,1.24,.16+.40*t];elbow=mix(shoulder,wrist,.5);elbow[0]=side*.32;}
            if(slug==='cable-woodchop') {wrist=[-.35+.7*t+side*.04,1.62-.72*t,.38];elbow=mix(shoulder,wrist,.5);}
            return {shoulder,elbow,wrist};
        });
    }
    return pose;
}

/** Equipment primitives are also used to fit cameras and verify attachments. */
export function exerciseEquipment(pose,slug,phase) {
    const items=[];
    const line=(points,color='#8fa8bc',width=1.5,role='frame')=>items.push({type:'line',points,color,width,role});
    const face=(points,color='#243b50')=>items.push({type:'face',points,color});
    const ball=(center,radii,color,role)=>items.push({type:'ellipsoid',center,radii,color,role});
    const box=(x0,x1,y0,y1,z0,z1)=>{
        face([[x0,y1,z0],[x1,y1,z0],[x1,y1,z1],[x0,y1,z1]],'#3a5870');
        face([[x0,y0,z0],[x0,y1,z0],[x0,y1,z1],[x0,y0,z1]]);
        face([[x0,y0,z0],[x1,y0,z0],[x1,y1,z0],[x0,y1,z0]],'#2c465c');
        face([[x1,y0,z0],[x1,y0,z1],[x1,y1,z1],[x1,y1,z0]]);
    };
    if(['ab-wheel-rollout','bird-dog','bicycle-crunch'].includes(slug)) {
        face([[-.48,-.005,-1.20],[.48,-.005,-1.20],[.48,-.005,1.22],[-.48,-.005,1.22]],'#101e2c');
        items.at(-1).role='ground';
    }
    if(slug==='box-jump') box(-.42,.42,0,.42,.44,1.06);
    if(slug==='broad-jump') for(const z of [0,1.05]) line([[-.42,.012,z],[.42,.012,z]],'#4ea7ff',1,'landing-mark');
    if(slug==='ab-wheel-rollout') {
        const center=mix(pose.arms[0].wrist,pose.arms[1].wrist,.5);
        ball(center,[.045,.16,.16],[51,78,99],'wheel');
        line([pose.arms[0].wrist,pose.arms[1].wrist],'#bdcede',4,'axle');
        const turn=center[2]/.16;
        for(let spoke=0;spoke<6;spoke++) {const a=turn+spoke*Math.PI/3;line([add(center,[-.048,0,0]),add(center,[-.048,.135*Math.cos(a),.135*Math.sin(a)])],'#7aaed0',1,'spoke');}
    }
    if(slug==='battle-ropes') {
        pose.arms.forEach((arm,index)=>{
            const anchor=[sides[index]*.18,.04,1.9];
            const points=Array.from({length:33},(_,i)=>{const q=i/32,p=mix(arm.wrist,anchor,q);p[1]=Math.max(.035,p[1]+.15*Math.sin(phase*Math.PI*4-q*15+index*Math.PI)*Math.sin(Math.PI*q));return p;});
            line(points,'#75b9de',3.5,'rope');ball(anchor,[.06,.04,.06],[78,98,113],'anchor');
        });
    }
    if(CABLE_DEMOS.includes(slug)) {
        const crossover=slug==='cable-crossover',sidePull=slug==='pallof-press'||slug==='cable-woodchop';
        const high=['cable-crossover','triceps-pushdown','straight-arm-pulldown','cable-woodchop','lat-pulldown'].includes(slug);
        const y=high?2.0:slug==='cable-curl'?.10:slug==='seated-cable-row'?.75:1.45;
        const anchors=pose.arms.map((arm,index)=>[crossover?sides[index]*.85:sidePull?-.85:sides[index]*.10,y,crossover?.12:sidePull?.10:1.02]);
        for(const x of crossover?[-.85,.85]:[sidePull?-.85:0]) {
            const z=crossover?.12:sidePull?.10:1.02;
            line([[x,.03,z],[x,2.10,z]],'#556f84',5);
            line([[x-.25,.03,z],[x+.25,.03,z]],'#556f84',4);
            box(x-.10,x+.10,.08,.37,z-.08,z+.08);
        }
        anchors.forEach((anchor,index)=>{
            ball(anchor,[.055,.055,.035],[84,116,141],'pulley');
            line([anchor,pose.arms[index].wrist],'#acd5ed',1.5,'cable');
            line([add(pose.arms[index].wrist,[-.055,0,0]),add(pose.arms[index].wrist,[.055,0,0])],'#b6cadd',3,'handle');
        });
        if(slug==='seated-cable-row') box(-.28,.28,.03,.45,-.20,.20);
        if(slug==='lat-pulldown') {box(-.28,.28,.03,.49,-.20,.20);line(pose.arms.map(arm=>arm.wrist),'#b6cadd',3,'bar');}
    }
    return items;
}
