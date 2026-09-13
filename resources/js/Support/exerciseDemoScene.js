/** Original articulated mannequin used to render the exercise GIFs and posters. */
const add = (a, b) => a.map((v, i) => v + b[i]);
const sub = (a, b) => a.map((v, i) => v - b[i]);
const mul = (a, n) => a.map((v) => v * n);
const dot = (a, b) => a.reduce((sum, v, i) => sum + v * b[i], 0);
const cross = (a, b) => [a[1]*b[2]-a[2]*b[1], a[2]*b[0]-a[0]*b[2], a[0]*b[1]-a[1]*b[0]];
const unit = (a) => mul(a, 1 / (Math.hypot(...a) || 1));
const mix = (a, b, t) => add(a, mul(sub(b, a), t));
const neutral = [111, 132, 159], blue = [63, 160, 232], violet = [131, 111, 196];

/** Two-bone inverse kinematics keeps elbows and knees connected to their endpoints. */
const joint = (start, end, upper, lower, pole) => {
    const direction = unit(sub(end, start));
    const distance = Math.min(upper + lower - .001, Math.max(.001, Math.hypot(...sub(end, start))));
    const along = (upper*upper - lower*lower + distance*distance) / (2*distance);
    const bend = unit(sub(pole, mul(direction, dot(pole, direction))));
    return add(add(start, mul(direction, along)), mul(bend, Math.sqrt(Math.max(0, upper*upper-along*along))));
};

export function exercisePose(slug, phase) {
    const t = (1-Math.cos(phase*Math.PI*2))/2;
    const pose = { pelvis:[0,.98,0], neck:[0,1.61,0], head:[0,1.76,0], arms:[], legs:[], prone:false, slug };
    for(const side of [-1,1]) {
        pose.arms.push({ shoulder:[side*.245,1.49,0], elbow:[side*.29,1.19,0], wrist:[side*.29,.91,.025] });
        pose.legs.push({ hip:[side*.12,.95,0], knee:[side*.135,.53,.035], ankle:[side*.14,.10,0] });
    }
    if(slug==='bench-press') {
        pose.pelvis=[0,.61,.22];pose.neck=[0,.65,-.47];pose.head=[0,.68,-.62];
        pose.arms= [-1,1].map(side=>{
            const shoulder=[side*.245,.66,-.34], wrist=[side*.32,1.20-.41*t,-.30+.13*t];
            return {shoulder,wrist,elbow:joint(shoulder,wrist,.30,.28,[side,-.5,.25])};
        });
        pose.legs= [-1,1].map(side=>({hip:[side*.12,.61,.22],knee:[side*.20,.47,.60],ankle:[side*.23,.075,.76]}));
    } else if(slug==='bodyweight-squat') {
        pose.pelvis=[0,.98-.39*t,-.24*t];pose.neck=add(pose.pelvis,[0,.63-.09*t,.22*t]);pose.head=add(pose.neck,[0,.15,.015]);
        pose.legs= [-1,1].map(side=>{
            const hip=add(pose.pelvis,[side*.12,-.025,0]),ankle=[side*.20,.10,.025];
            return {hip,ankle,knee:joint(hip,ankle,.43,.43,[side*.2,0,1])};
        });
        pose.arms= [-1,1].map(side=>{
            const shoulder=add(pose.neck,[side*.245,-.12,0]),wrist=add(pose.neck,[side*.20,-.12,.48]);
            return {shoulder,wrist,elbow:joint(shoulder,wrist,.30,.28,[side,-1,0])};
        });
    } else if(slug==='dumbbell-curl') {
        pose.arms.forEach((arm)=> {arm.elbow=[arm.shoulder[0]*1.12,1.18,.015];const angle=.08+2.2*t;arm.wrist=add(arm.elbow,[0,-.28*Math.cos(angle),.28*Math.sin(angle)]);});
    } else if(slug==='push-up' || slug==='plank') {
        pose.prone=true;const drop=slug==='push-up'?.22*t:.006*t;
        pose.pelvis=[0,.40-drop*.6,.24];pose.neck=[0,.57-drop,-.51];pose.head=[0,.59-drop,-.68];
        pose.legs= [-1,1].map(side=>{
            const hip=add(pose.pelvis,[side*.12,0,0]),ankle=[side*.13,.085,.98];
            return {hip,ankle,knee:mix(hip,ankle,.5)};
        });
        pose.arms= [-1,1].map(side=>{
            const shoulder=[side*.245,.51-drop,-.43];
            if(slug==='plank') { shoulder[1]=.42-drop;return {shoulder,elbow:[side*.245,.09,-.43],wrist:[side*.245,.065,-.72]}; }
            const wrist=[side*.28,.07,-.46];return {shoulder,wrist,elbow:joint(shoulder,wrist,.29,.27,[side,0,.4])};
        });
        if(slug==='plank') {pose.neck[1]-=.12;pose.head[1]-=.12;pose.pelvis[1]-=.06;pose.legs.forEach((leg)=>{leg.hip[1]-=.06;leg.knee=mix(leg.hip,leg.ankle,.5);});}
    } else if(slug==='steady-state-run') {
        const bob=.022*Math.cos(phase*Math.PI*4);pose.pelvis[1]+=bob;pose.neck=add(pose.neck,[0,bob,.06]);pose.head=add(pose.head,[0,bob,.06]);
        pose.legs.forEach((leg,i)=>{
            const wave=Math.sin(phase*Math.PI*2+i*Math.PI+Math.PI/2), angle=.65*wave;
            leg.hip[1]+=bob;leg.knee=add(leg.hip,[0,-.43*Math.cos(angle),.43*Math.sin(angle)]);
            const lower=angle-Math.max(0,-wave)*1.15;
            leg.ankle=add(leg.knee,[0,-.43*Math.cos(lower),.43*Math.sin(lower)]);
        });
        pose.arms.forEach((arm,i)=>{
            const angle=-.6*Math.sin(phase*Math.PI*2+i*Math.PI+Math.PI/2);arm.shoulder=add(arm.shoulder,[0,bob,.06]);
            arm.elbow=add(arm.shoulder,[0,-.28*Math.cos(angle),.28*Math.sin(angle)]);
            arm.wrist=add(arm.elbow,[0,.08,.25]);
        });
    }
    return pose;
}

/** Draws a lit triangle mesh with a fixed camera; no runtime 3D library is required. */
export function drawExerciseDemo(canvas, slug, phase) {
    const ctx=canvas.getContext('2d'), pose=exercisePose(slug,phase);
    const horizontal=slug==='bench-press'||pose.prone;
    const yaw=horizontal?-1.02:.48, pitch=horizontal?.24:.12, scale=horizontal?120:111;
    const center=horizontal?[0,slug==='bench-press'?.65:.40,.12]:[0,.97,0];
    const cy=Math.cos(yaw),sy=Math.sin(yaw),cp=Math.cos(pitch),sp=Math.sin(pitch);
    const camera=p=>{const q=sub(p,center),depth=q[0]*sy+q[2]*cy;return [200+(q[0]*cy-q[2]*sy)*scale,121+(-q[1]*cp+depth*sp)*scale,depth*cp+q[1]*sp];};
    const view=unit([sy,sp,cy]),light=unit([-.6,.85,.9]);
    const faces=[];
    const plank = slug === 'plank';
    const ellipsoid=(position,radii,color=neutral,basis=[[1,0,0],[0,1,0],[0,0,1]])=>{
        const latitude=plank?12:18,longitude=plank?18:28, vertices=[];
        for(let a=0;a<=latitude;a++) {
            const theta=Math.PI*a/latitude;
            for(let b=0;b<=longitude;b++) {
                const phi=2*Math.PI*b/longitude;
                const n=[Math.sin(theta)*Math.cos(phi),Math.cos(theta),Math.sin(theta)*Math.sin(phi)];
                const local=n.map((v,i)=>v*radii[i]);
                const point=add(position,add(add(mul(basis[0],local[0]),mul(basis[1],local[1])),mul(basis[2],local[2])));
                const normal=unit(add(add(mul(basis[0],n[0]/radii[0]),mul(basis[1],n[1]/radii[1])),mul(basis[2],n[2]/radii[2])));
                vertices.push({point:camera(point),normal});
            }
        }
        for(let a=0;a<latitude;a++) for(let b=0;b<longitude;b++) {
            const i=a*(longitude+1)+b;
            for(const ids of [[i,i+1,i+longitude+1],[i+1,i+longitude+2,i+longitude+1]]) {
                const points=ids.map(k=>vertices[k]),normal=unit(points.reduce((n,v)=>add(n,v.normal),[0,0,0]));
                if(dot(normal,view)<-.04)continue;
                const diffuse=Math.max(0,dot(normal,light)),spec=Math.pow(Math.max(0,dot(normal,unit(add(light,view)))),24)*.20;
                const rim=Math.pow(1-Math.max(0,dot(normal,view)),3)*.15;
                const fill=color.map((v,i)=>Math.round(Math.min(255,v*(.30+.66*diffuse)+spec*210+rim*[65,125,190][i])));
                faces.push({points:points.map(v=>v.point),depth:points.reduce((sum,v)=>sum+v.point[2],0)/3,fill:'rgb('+fill.join(',')+')'});
            }
        }
    };
    const bone=(a,b,width,depth=width,color=neutral)=>{
        const axis=unit(sub(b,a)), reference=Math.abs(axis[2])<.9?[0,0,1]:[0,1,0];
        const right=unit(cross(axis,reference)),forward=unit(cross(right,axis));
        ellipsoid(mix(a,b,.5),[width,Math.hypot(...sub(b,a))*.56,depth],color,[right,axis,forward]);
    };
    const up=unit(sub(pose.neck,pose.pelvis)),right=[1,0,0];
    const forward=mul(unit(cross(right,up)),pose.prone?-1:1),basis=[right,up,forward];
    const body=(height,x,depth)=>add(mix(pose.pelvis,pose.neck,height),add(mul(right,x),mul(forward,depth)));
    const chestFocus=['bench-press','push-up'].includes(slug),legsFocus=slug==='bodyweight-squat',coreFocus=slug==='plank';
    if (plank) {
        // Continuous hip-to-neck surface avoids the disconnected mannequin segments.
        const rings = [[-.14,.08,.07],[-.05,.145,.10],[.08,.17,.115],[.22,.155,.103],[.36,.148,.097],[.50,.173,.108],[.64,.20,.12],[.76,.215,.113],[.86,.18,.095],[.96,.09,.065],[1.08,.052,.052]];
        const segments = 24;
        const vertices = rings.map(([height,width,depth]) => Array.from({length:segments}, (_,index) => {
            const angle=index/segments*Math.PI*2;
            const point=body(height,Math.cos(angle)*width,Math.sin(angle)*depth);
            const normal=unit(add(mul(right,Math.cos(angle)/width),mul(forward,Math.sin(angle)/depth)));
            return {point:camera(point),normal};
        }));
        for(let ring=0;ring<rings.length-1;ring++) {
            for(let index=0;index<segments;index++) {
                const next=(index+1)%segments;
                const points=[vertices[ring][index],vertices[ring][next],vertices[ring+1][next],vertices[ring+1][index]];
                const normal=unit(points.reduce((sum,vertex)=>add(sum,vertex.normal),[0,0,0]));
                if(dot(normal,view)<0) continue;
                const core=ring>=2 && ring<=5;
                const color=core?blue:neutral;
                const shade=.34+.66*Math.max(0,dot(normal,light));
                faces.push({points:points.map(vertex=>vertex.point),depth:points.reduce((sum,vertex)=>sum+vertex.point[2],0)/4,fill:`rgb(${color.map(value=>Math.round(value*shade)).join(',')})`});
            }
        }
    } else {
    ellipsoid(body(.52,0,0),[.215,.235,.115],neutral,basis);
    ellipsoid(body(.18,0,0),[.15,.16,.105],neutral,basis);
    ellipsoid(pose.pelvis,[.166,.13,.113],[62,78,102],basis);
    bone(body(.88,0,0),add(pose.neck,mul(up,.07)),.055,.053);
    for(const side of [-1,1]) {
        ellipsoid(body(.64,side*.105,.096),[.108,.094,.045],chestFocus?blue:neutral,basis);
        ellipsoid(body(.13,side*.105,-.07),[.085,.115,.069],legsFocus?violet:neutral,basis);
        for(let row=0;row<3;row++) ellipsoid(body(.25+row*.13,side*.045,.107),[.040,.039,.022],coreFocus?blue:neutral,basis);
        ellipsoid(body(.36,side*.139,.03),[.044,.14,.065],coreFocus?violet:neutral,basis);
    }
    }
    ellipsoid(pose.head,[.087,.12,.085],neutral,basis);
    ellipsoid(add(pose.head,mul(forward,.083)),[.016,.025,.025],neutral,basis);
    for(const side of [-1,1]) {
        ellipsoid(add(pose.head,mul(right,side*.086)),[.016,.029,.017],neutral,basis);
        ellipsoid(add(add(pose.head,mul(forward,.077)),add(mul(right,side*.035),mul(up,.016))),[.020,.006,.008],[23,34,49],basis);
    }
    pose.arms.forEach((arm)=>{
        ellipsoid(arm.shoulder,[.084,.087,.086],chestFocus?violet:neutral,basis);
        bone(arm.shoulder,arm.elbow,.063,.065,chestFocus?violet:neutral);
        if (!plank) bone(add(mix(arm.shoulder,arm.elbow,.18),mul(forward,.032)),add(mix(arm.shoulder,arm.elbow,.82),mul(forward,.034)),.042,.034,slug==='dumbbell-curl'?blue:neutral);
        ellipsoid(arm.elbow,[.044,.046,.043]);
        bone(arm.elbow,arm.wrist,.047,.039);
        const handAxis=unit(sub(arm.wrist,arm.elbow)),handEnd=add(arm.wrist,mul(handAxis,.085));
        if (slug==='bench-press' || slug==='dumbbell-curl') {
            ellipsoid(arm.wrist,[.043,.039,.038]);
            ellipsoid(add(arm.wrist,[.027,.017,.012]),[.014,.025,.018]);
        } else {
            bone(arm.wrist,handEnd,.034,.025);
            for(let finger=0;finger<4;finger++) {
                const at=add(handEnd,[(finger-1.5)*.014,0,0]);bone(at,add(at,mul(handAxis,.037)),.007,.008);
            }
        }
        if(slug==='dumbbell-curl') {
            const a=add(arm.wrist,[-.095,0,0]),b=add(arm.wrist,[.095,0,0]);bone(a,b,.015,.015,[150,168,190]);
            for(const point of [a,b])ellipsoid(point,[.027,.068,.068],[77,92,119]);
        }
    });
    pose.legs.forEach((leg)=>{
        bone(leg.hip,leg.knee,.092,.097,legsFocus?blue:neutral);
        if (!plank) bone(add(mix(leg.hip,leg.knee,.15),mul(forward,.044)),add(mix(leg.hip,leg.knee,.84),mul(forward,.04)),.066,.058,legsFocus?blue:neutral);
        ellipsoid(leg.knee,[.058,.059,.061]);
        bone(leg.knee,leg.ankle,.059,.053);
        if (!plank) bone(add(mix(leg.knee,leg.ankle,.12),[0,0,-.035]),add(mix(leg.knee,leg.ankle,.60),[0,0,-.03]),.058,.05,slug==='steady-state-run'?blue:neutral);
        ellipsoid(leg.ankle,[.036,.038,.042]);
        if (plank) {
            bone(leg.ankle,add(leg.ankle,[0,-.06,.13]),.047,.033);
            ellipsoid(add(leg.ankle,[0,-.06,.13]),[.05,.026,.035]);
        } else {
            ellipsoid(add(leg.ankle,[0,-.035,pose.prone?.025:.07]),[.055,.045,.108],[60,78,103]);
        }
    });
    if(slug==='bench-press') {
        bone([0,.47,-.72],[0,.47,.39],.155,.055,[38,54,77]);
        for(const z of [-.56,.28]) {bone([0,.045,z],[0,.43,z],.033,.033,[75,90,115]);bone([-.29,.045,z],[.29,.045,z],.025,.025,[75,90,115]);}
        const barY=pose.arms[0].wrist[1],barZ=pose.arms[0].wrist[2];
        bone([-.72,barY,barZ],[.72,barY,barZ],.012,.012,[188,202,222]);
        for(const x of [-.59,.59]) {ellipsoid([x,barY,barZ],[.036,.146,.146],[59,75,101]);ellipsoid([x*1.055,barY,barZ],[.012,.044,.044],[151,169,194]);}
    }
    ctx.fillStyle='#080e1c';ctx.fillRect(0,0,400,240);
    const glow=ctx.createRadialGradient(200,105,10,200,120,190);glow.addColorStop(0,'rgba(49,78,122,.14)');glow.addColorStop(1,'rgba(8,14,28,0)');ctx.fillStyle=glow;ctx.fillRect(0,0,400,240);
    const ground=camera([0,0,.12]);
    ctx.fillStyle='rgba(0,0,0,.35)';ctx.beginPath();ctx.ellipse(ground[0],ground[1],horizontal?105:53,9,0,0,Math.PI*2);ctx.fill();
    faces.sort((a,b)=>a.depth-b.depth);
    for(const face of faces) {ctx.beginPath();face.points.forEach((p,i)=>i?ctx.lineTo(p[0],p[1]):ctx.moveTo(p[0],p[1]));ctx.closePath();ctx.fillStyle=face.fill;ctx.strokeStyle=plank?'rgba(115,193,245,.28)':face.fill;ctx.lineWidth=plank?.45:.35;ctx.fill();ctx.stroke();}
}
