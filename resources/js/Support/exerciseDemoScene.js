/** Original articulated mannequin used to render the exercise GIFs and posters. */
import { archetypeFor, focusFor, loadFor } from './exerciseArchetypes.js';
import { correctExercisePose, exerciseEquipment } from './exerciseCorrections.js';
import { FORM_DEMOS } from './exerciseFormCorrections.js';
import { MOTION_DEMOS } from './exerciseMotionRefinements.js';

const AUTHORED_LOADS = [...FORM_DEMOS, ...MOTION_DEMOS];

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
    } else if(slug==='incline-treadmill-walk') {
        pose.pelvis=[0,1.05+.008*Math.cos(phase*Math.PI*4),0];
        pose.neck=add(pose.pelvis,[0,.63,.055]);
        pose.head=add(pose.neck,[0,.15,.013]);
        pose.legs=[-1,1].map((side,index)=>{
            const cycle=((phase+index*.5)%1+1)%1;
            const swing=Math.max(0,(cycle-.6)/.4);
            const z=cycle<.6?.24-.48*cycle/.6:-.24+.48*(swing*swing*(3-2*swing));
            const lift=cycle<.6?0:.085*Math.sin(Math.PI*swing);
            const hip=add(pose.pelvis,[side*.12,-.025,0]);
            const ankle=[side*.13,treadmillBeltHeight(z)+.08+lift,z];
            return {hip,ankle,knee:joint(hip,ankle,.43,.43,[0,0,1])};
        });
        pose.arms=[-1,1].map((side,index)=>{
            const swing=-.16*Math.cos(phase*Math.PI*2+index*Math.PI);
            const shoulder=add(pose.neck,[side*.235,-.12,0]);
            const elbow=add(shoulder,[side*.025,-.29,swing]);
            return {shoulder,elbow,wrist:add(elbow,[0,-.22,.10])};
        });
    } else if(archetypeFor(slug)) {
        archetypePose(pose, archetypeFor(slug), t, phase);
    }
    return correctExercisePose(pose,slug,phase);
}

/** Segment lengths shared by every archetype, in the same units as the base pose. */
const UPPER_ARM=.30, FOREARM=.28, THIGH=.43, SHIN=.43;

/**
 * Places both arms from a wrist position, letting IK find the elbow.
 *
 * The shoulder is always derived from the current neck. Reusing whatever was
 * in pose.arms would keep the standing default, which leaves the arms floating
 * a metre above the body in any lying-down archetype.
 */
const reachArms = (pose, wristFor, poleFor) => {
    pose.arms = [-1,1].map((side) => {
        const shoulder = add(pose.neck,[side*.245,-.12,0]);
        const wrist = wristFor(side, shoulder);
        return { shoulder, wrist, elbow: joint(shoulder, wrist, UPPER_ARM, FOREARM, poleFor(side)) };
    });
};

/** Places both legs from an ankle position, letting IK find the knee. */
const standLegs = (pose, ankleFor, poleFor=(side)=>[side*.2,0,1]) => {
    pose.legs = [-1,1].map((side) => {
        const hip = add(pose.pelvis,[side*.12,-.025,0]);
        const ankle = ankleFor(side);
        return { hip, ankle, knee: joint(hip, ankle, THIGH, SHIN, poleFor(side)) };
    });
};

/** Hangs the arms straight down from the shoulders, as when holding a load. */
const hangArms = (pose, reach=.58, spread=.02, depth=0) =>
    reachArms(pose, (side, shoulder) => [shoulder[0]+side*spread, shoulder[1]-reach, depth], (side) => [side,-1,.3]);

/**
 * Builds the pose for an archetype in place.
 *
 * Each branch moves the pelvis/neck/head along the motion path, then places
 * limbs from their endpoints so the IK keeps joints connected. `t` sweeps
 * 0 → 1 → 0 across the loop, so the movement returns to where it began.
 */
function archetypePose(pose, spec, t, phase) {
    const { motion, load, variant } = spec;
    const upright = () => { pose.neck = add(pose.pelvis,[0,.63,0]); pose.head = add(pose.neck,[0,.15,.015]); };

    /** Torso pitched forward about the hips, as in a hinge or a row. */
    const pitchTorso = (angle) => {
        pose.neck = add(pose.pelvis,[0,.63*Math.cos(angle),.63*Math.sin(angle)]);
        pose.head = add(pose.neck,[0,.15*Math.cos(angle),.15*Math.sin(angle)]);
    };

    /** Where the hands sit for a squat, by what they are holding. */
    const squatHands = () => {
        if(load==='barbell' && variant==='front') return reachArms(pose,(side)=>add(pose.neck,[side*.20,-.02,.13]),(side)=>[side,-1,1]);
        if(load==='barbell') return reachArms(pose,(side)=>add(pose.neck,[side*.40,-.05,-.02]),(side)=>[side,-1,-.6]);
        if(load==='dumbbell') return reachArms(pose,(side)=>add(pose.neck,[side*.09,-.28,.20]),(side)=>[side,-1,.6]);
        if(load==='machine') return hangArms(pose,.5,.06,.12);
        return reachArms(pose,(side)=>add(pose.neck,[side*.20,-.12,.48]),(side)=>[side,-1,0]);
    };

    if(motion==='squat') {
        const depth=(variant==='seated'?.30:.39)*t, wide=variant==='lateral'?.34:.20;
        pose.pelvis=[0,.98-depth,-.24*t]; upright(); pose.neck=add(pose.pelvis,[0,.63-.09*t,.22*t]); pose.head=add(pose.neck,[0,.15,.015]);
        standLegs(pose,(side)=>[side*wide,.10,.025]);
        squatHands();
    } else if(motion==='hinge') {
        const angle=(variant==='high'?.72:.98)*t, wide=variant==='wide'?.30:.16;
        pose.pelvis=[0,.95-.05*t,-.26*t]; pitchTorso(angle);
        if(variant==='singleleg') {
            pose.legs=[-1,1].map((side,index)=>{
                const hip=add(pose.pelvis,[side*.12,-.025,0]);
                const ankle=index?[side*.14,.10+.55*t,-.62*t]:[side*.16,.10,0];
                return {hip,ankle,knee:joint(hip,ankle,THIGH,SHIN,[side*.2,0,1])};
            });
        } else standLegs(pose,(side)=>[side*wide,.10,.02]);
        if(variant==='back') reachArms(pose,(side)=>add(pose.neck,[side*.40,-.05,-.02]),(side)=>[side,-1,-.6]);
        else hangArms(pose,.58,.03,.06);
    } else if(motion==='lunge') {
        const drop=.34*t, lift=variant==='step'?.22*t:0;
        pose.pelvis=[0,.98-drop+lift,0]; upright();
        pose.legs=[-1,1].map((side,index)=>{
            const hip=add(pose.pelvis,[side*.12,-.025,0]);
            const ankle=index?[side*.16,.10+lift,-.44]:[side*.16,.10+(variant==='rear'?.26*t:0),.46];
            return {hip,ankle,knee:joint(hip,ankle,THIGH,SHIN,[side*.2,0,1])};
        });
        hangArms(pose,.58,.05,.02);
    } else if(motion==='bench'||motion==='fly') {
        // Supine on a bench; the bench tilt shifts the shoulders, not the motion.
        const tilt=variant==='incline'?.30:variant==='decline'?-.18:0;
        pose.horizontal=true;
        pose.pelvis=[0,.61,.22]; pose.neck=[0,.65+tilt*.30,-.47]; pose.head=[0,.68+tilt*.34,-.62];
        pose.legs=[-1,1].map((side)=>({hip:[side*.12,.61,.22],knee:[side*.20,.47,.60],ankle:[side*.23,.075,.76]}));
        if(motion==='fly') {
            const open=.95*t;
            reachArms(pose,(side)=>[side*(.30+.52*open),1.16-.30*open+tilt*.3,-.34+(variant==='pullover'?-.36*open:0)],(side)=>[side,-.4,.3]);
        } else {
            reachArms(pose,(side)=>[side*(load==='dumbbell'?.34:.32),1.20-.41*t+tilt*.3,-.30+.13*t],(side)=>[side,-.5,.25]);
        }
    } else if(motion==='pushup') {
        pose.prone=true;
        const pike=variant==='pike', drop=(pike?.14:.22)*t;
        pose.pelvis=[0,(pike?.62:.40)-drop*.5,(pike?.44:.24)]; pose.neck=[0,(pike?.46:.57)-drop,-.51]; pose.head=[0,(pike?.42:.59)-drop,-.68];
        pose.legs=[-1,1].map((side,index)=>{
            const hip=add(pose.pelvis,[side*.12,0,0]);
            const ankle=variant==='climber'&&index?[side*.13,.30,.30]:[side*.13,.085,pike?.78:.98];
            return {hip,ankle,knee:mix(hip,ankle,.5)};
        });
        const spread=variant==='narrow'?.17:.28;
        reachArms(pose,(side)=>[side*spread,.07,-.46],(side)=>[side,0,.4]);
    } else if(motion==='dip') {
        const drop=.30*t;
        pose.pelvis=[0,1.00-drop,variant==='bench'?.26:0]; pitchTorso(variant==='bench'?-.10:.18);
        standLegs(pose,(side)=>[side*.13,.22-drop*.2,variant==='bench'?-.52:.30],(side)=>[side*.2,0,1]);
        reachArms(pose,(side)=>[side*.30,pose.pelvis[1]-.42+drop,variant==='bench'?.30:.04],(side)=>[side,-.4,-1]);
    } else if(motion==='press') {
        pose.pelvis=[0,.98,0]; upright();
        standLegs(pose,(side)=>[side*.14,.10,.01]);
        const height=.30+.62*t, out=variant==='single'?.12:.26;
        reachArms(pose,(side,shoulder)=>[side*(out+.06*(1-t)),shoulder[1]+height,-.02-.10*(1-t)],(side)=>[side,-.2,.6]);
        if(variant==='single') pose.arms[0]={...pose.arms[0],wrist:add(pose.arms[0].shoulder,[-.05,-.55,.05]),elbow:add(pose.arms[0].shoulder,[-.06,-.28,.03])};
    } else if(motion==='row') {
        const pull=.34*t;
        pose.pelvis=[0,variant==='seated'?.62:.92,variant==='seated'?.10:-.20]; pitchTorso(variant==='seated'?.20:1.02);
        if(variant==='seated') pose.legs=[-1,1].map((side)=>({hip:add(pose.pelvis,[side*.12,-.02,0]),knee:[side*.18,.50,.48],ankle:[side*.20,.14,.78]}));
        else if(variant==='supine') { pose.prone=false; standLegs(pose,(side)=>[side*.14,.10,.62]); }
        else standLegs(pose,(side)=>[side*.15,.10,.02]);
        reachArms(pose,(side,shoulder)=>[side*.26,shoulder[1]-.56+pull*.9,shoulder[2]+.34-pull*1.0],(side)=>[side,-1,.2]);
        if(variant==='single') pose.arms[0]={...pose.arms[0],wrist:add(pose.arms[0].shoulder,[-.30,-.12,.34]),elbow:add(pose.arms[0].shoulder,[-.30,-.10,.06])};
    } else if(motion==='pulldown') {
        const pull=.40*t;
        pose.pelvis=[0,(variant==='seated'?.66:1.02)+pull*.30,0]; upright();
        if(variant==='seated') pose.legs=[-1,1].map((side)=>({hip:add(pose.pelvis,[side*.12,-.02,0]),knee:[side*.18,.50,.36],ankle:[side*.20,.12,.62]}));
        else standLegs(pose,(side)=>[side*.13,.10+.40*t,.30*t]);
        reachArms(pose,(side,shoulder)=>[side*(.40-.10*t),shoulder[1]+.56-pull*1.5,-.04],(side)=>[side,-.3,.6]);
    } else if(motion==='curl') {
        const seatedish=variant==='seated'||variant==='preacher'||variant==='incline';
        pose.pelvis=[0,seatedish?.66:.98,0]; upright();
        if(seatedish) pose.legs=[-1,1].map((side)=>({hip:add(pose.pelvis,[side*.12,-.02,0]),knee:[side*.17,.50,.34],ankle:[side*.18,.12,.60]}));
        else standLegs(pose,(side)=>[side*.14,.10,.01]);
        const angle=.08+(variant==='wrist'?.9:2.2)*t;
        pose.arms=[-1,1].map((side,index)=>{
            const shoulder=add(pose.neck,[side*.245,-.12,0]);
            const elbow=variant==='preacher'?add(shoulder,[side*.03,-.20,.34]):[shoulder[0]*1.12,shoulder[1]-.31,.015];
            const wrist=add(elbow,[0,-FOREARM*Math.cos(angle),FOREARM*Math.sin(angle)]);
            return index===0&&variant==='seated' ? {shoulder,elbow,wrist:add(elbow,[0,-FOREARM,.02])} : {shoulder,elbow,wrist};
        });
    } else if(motion==='extension') {
        pose.pelvis=[0,variant==='supine'?.61:.98,variant==='supine'?.22:0];
        if(variant==='supine') { pose.horizontal=true; pose.neck=[0,.65,-.47]; pose.head=[0,.68,-.62];
            pose.legs=[-1,1].map((side)=>({hip:[side*.12,.61,.22],knee:[side*.20,.47,.60],ankle:[side*.23,.075,.76]}));
            reachArms(pose,(side)=>[side*.20,1.16-.26*(1-t),-.34+.20*(1-t)],(side)=>[side,-.5,.2]);
        } else {
            upright(); standLegs(pose,(side)=>[side*.14,.10,.01]);
            const overhead=variant==='overhead';
            pose.arms=[-1,1].map((side)=>{
                const shoulder=add(pose.neck,[side*.245,-.12,0]);
                const elbow=overhead?add(shoulder,[side*.06,.26,-.02]):add(shoulder,[side*.04,-.30,.02]);
                const reach=overhead?[0,-FOREARM*(1-1.7*t),-.10]:[0,-FOREARM*(.30+.70*t),.16*(1-t)];
                return {shoulder,elbow,wrist:add(elbow,reach)};
            });
        }
    } else if(motion==='raise') {
        pose.pelvis=[0,.98,0];
        if(variant==='bent') pitchTorso(.92); else upright();
        standLegs(pose,(side)=>[side*.14,.10,.01]);
        const lift=t, front=variant==='front', face=variant==='face', up=variant==='upright', battle=variant==='battle';
        reachArms(pose,(side,shoulder)=>{
            if(front) return [side*.16,shoulder[1]-.52+1.00*lift,-.46*lift-.06];
            if(face) return [side*(.22+.20*lift),shoulder[1]+.06*lift,-.30-.16*lift];
            if(up) return [side*.09,shoulder[1]-.50+.46*lift,-.14];
            if(battle) return [side*.26,shoulder[1]-.46+.30*Math.sin(phase*Math.PI*4+(side>0?Math.PI:0)),-.22];
            if(variant==='straightarm') return [side*.20,shoulder[1]+.30-.80*lift,-.30];
            return [side*(.26+.46*lift),shoulder[1]-.50+.54*lift,-.04];
        },(side)=>[side,-.6,.2]);
    } else if(motion==='bridge') {
        // Shoulders anchored, hips drive up, shins stay vertical over the heels.
        const lift=.26*t, shoulderHeight=variant==='bench'?.46:.16;
        pose.horizontal=true;
        pose.pelvis=[0,.24+lift,.12]; pose.neck=[0,shoulderHeight,-.46]; pose.head=[0,shoulderHeight+.05,-.62];
        pose.legs=[-1,1].map((side)=>{
            const hip=add(pose.pelvis,[side*.12,0,0]), ankle=[side*.16,.085,.46];
            return {hip,ankle,knee:[side*.15,hip[1]+.01,.20]};
        });
        // The bar rests across the hips, so the hands sit there rather than on the floor.
        if(load==='barbell') reachArms(pose,(side)=>[side*.24,pose.pelvis[1]+.10,.06],(side)=>[side,-.6,-.4]);
        else reachArms(pose,(side)=>[side*.30,.09,-.28],(side)=>[side,-.3,-.4]);
    } else if(motion==='legcurl') {
        const flex=(variant==='kneeling'?.9:1.5)*t;
        pose.prone=variant!=='kneeling';
        if(variant==='kneeling') {
            pose.pelvis=[0,.56+.10*(1-t),-.20*t]; pitchTorso(.55*t);
            pose.legs=[-1,1].map((side)=>({hip:add(pose.pelvis,[side*.12,0,0]),knee:[side*.13,.10,.16],ankle:[side*.13,.085,.52]}));
            hangArms(pose,.50,.06,-.18);
        } else {
            pose.pelvis=[0,.24,.18]; pose.neck=[0,.26,-.48]; pose.head=[0,.27,-.64];
            pose.legs=[-1,1].map((side)=>{const hip=add(pose.pelvis,[side*.12,0,0]),knee=add(hip,[0,.02,.43]);
                return {hip,knee,ankle:add(knee,[0,SHIN*Math.sin(flex),SHIN*Math.cos(flex)])};});
            reachArms(pose,(side)=>[side*.30,.22,-.56],(side)=>[side,0,-.4]);
        }
    } else if(motion==='legextension') {
        pose.pelvis=[0,.66,.10]; upright();
        pose.legs=[-1,1].map((side)=>{const hip=add(pose.pelvis,[side*.12,-.02,0]),knee=add(hip,[side*.04,-.10,.40]);
            const angle=1.35-1.25*t;
            return {hip,knee,ankle:add(knee,[0,-SHIN*Math.cos(angle),SHIN*Math.sin(angle)])};});
        reachArms(pose,(side)=>add(pose.pelvis,[side*.28,.02,-.16]),(side)=>[side,-1,-.4]);
    } else if(motion==='calf') {
        const rise=.14*t, seated=variant==='seated';
        pose.pelvis=[0,(seated?.66:.98)+ (seated?0:rise),0]; upright();
        pose.legs=[-1,1].map((side)=>{const hip=add(pose.pelvis,[side*.12,-.025,0]);
            const ankle=seated?[side*.16,.14,.46]:[side*.14,.10+rise,.02];
            return {hip,ankle,knee:seated?add(hip,[side*.03,-.12,.34]):joint(hip,ankle,THIGH,SHIN,[side*.2,0,1])};});
        hangArms(pose,.56,.06,seated?-.04:.02);
    } else if(motion==='hold') {
        holdPose(pose, variant, t);
    } else if(motion==='crunch'||motion==='legraise'||motion==='twist'||motion==='rollout') {
        corePose(pose, motion, variant, t, phase);
    } else if(motion==='run'||motion==='cycle'||motion==='rowmachine') {
        cyclicPose(pose, motion, variant, t, phase);
    } else if(motion==='jump'||motion==='swing') {
        const launch=motion==='swing'?0:Math.max(0,Math.sin(phase*Math.PI*2));
        const crouch=motion==='swing'?t:Math.max(0,-Math.sin(phase*Math.PI*2));
        pose.pelvis=[0,.98-.34*crouch+.30*launch,-.20*crouch];
        if(motion==='swing') pitchTorso(.85*crouch); else upright();
        standLegs(pose,(side)=>[side*.17,.10+.30*launch,.02]);
        if(motion==='swing') reachArms(pose,(side)=>add(pose.pelvis,[side*.07,-.34+.70*(1-crouch),.30+.30*(1-crouch)]),(side)=>[side,-1,.4]);
        else reachArms(pose,(side,shoulder)=>[side*.26,shoulder[1]-.50+1.00*launch-.20*crouch,-.30*launch+.34*crouch],(side)=>[side,-.6,.3]);
    } else if(motion==='carry') {
        const step=Math.sin(phase*Math.PI*2);
        pose.pelvis=[0,.98+.015*Math.cos(phase*Math.PI*4),0]; upright();
        pose.legs=[-1,1].map((side,index)=>{const hip=add(pose.pelvis,[side*.12,-.025,0]);
            const swing=index?step:-step;
            return {hip,ankle:[side*.14,.10+Math.max(0,swing)*.10,-.24*swing],knee:joint(hip,[side*.14,.10+Math.max(0,swing)*.10,-.24*swing],THIGH,SHIN,[side*.2,0,1])};});
        hangArms(pose,.60,.09,.02);
        if(variant==='single') pose.arms[0]={...pose.arms[0],wrist:add(pose.arms[0].shoulder,[-.10,-.34,.12]),elbow:add(pose.arms[0].shoulder,[-.08,-.18,.06])};
    } else if(motion==='quadruped') {
        pose.prone=true;
        const arch=variant==='thoracic'?0:.10*t;
        pose.pelvis=[0,.56,.30]; pose.neck=[0,.56-arch*1.6,-.34]; pose.head=[0,.54-arch*2.0,-.50];
        pose.legs=[-1,1].map((side,index)=>{const hip=add(pose.pelvis,[side*.12,0,0]);
            const extend=variant==='birddog'&&index===0;
            const ankle=extend?[side*.10,.52,.80]:[side*.13,.085,.36];
            return {hip,ankle,knee:extend?mix(hip,ankle,.5):add(hip,[side*.01,-.24,.12])};});
        pose.arms=[-1,1].map((side,index)=>{
            const shoulder=add(pose.neck,[side*.22,-.02,.02]);
            const extend=(variant==='birddog'&&index===1)||(variant==='thoracic'&&index===1);
            const wrist=extend?add(shoulder,[side*.10,variant==='thoracic'?.42:.30,-.52]):[side*.22,.085,-.36];
            return {shoulder,wrist,elbow:joint(shoulder,wrist,UPPER_ARM,FOREARM,[side,extend?.6:-.4,.2])};});
    } else if(motion==='stretch') {
        stretchPose(pose, variant, t);
    }
}

/** Isometric positions; `t` only adds a small breathing sway. */
function holdPose(pose, variant, t) {
    const sway=.012*t;
    if(variant==='inverted') {
        pose.pelvis=[0,1.12+sway,0]; pose.neck=[0,.50,.04]; pose.head=[0,.34,.06];
        pose.legs=[-1,1].map((side)=>({hip:[side*.12,1.12,0],knee:[side*.13,1.54,.02],ankle:[side*.13,1.95,.02]}));
        pose.arms=[-1,1].map((side)=>({shoulder:[side*.245,.50,.02],elbow:[side*.25,.30,.03],wrist:[side*.25,.085,.04]}));
        return;
    }
    if(variant==='side') {
        pose.prone=true;
        pose.pelvis=[0,.34+sway,.20]; pose.neck=[0,.44,-.46]; pose.head=[0,.46,-.62];
        pose.legs=[-1,1].map((side)=>({hip:[side*.06,.32,.22],knee:[side*.06,.22,.62],ankle:[side*.06,.10,.98]}));
        pose.arms=[-1,1].map((side,index)=>index
            ? {shoulder:[.06,.46,-.42],elbow:[.10,.80,-.40],wrist:[.12,1.10,-.38]}
            : {shoulder:[-.10,.42,-.42],elbow:[-.14,.16,-.40],wrist:[-.16,.075,-.68]});
        return;
    }
    if(variant==='hollow'||variant==='deadbug') {
        pose.horizontal=true;
        const lift=variant==='deadbug'?.30*t:0;
        pose.pelvis=[0,.20,.16]; pose.neck=[0,.30,-.44]; pose.head=[0,.34,-.58];
        pose.legs=[-1,1].map((side,index)=>{const hip=add(pose.pelvis,[side*.12,0,0]);
            const raise=variant==='deadbug'&&index?lift:0;
            return {hip,knee:add(hip,[0,.34+raise,.26]),ankle:add(hip,[0,.30+raise*1.4,.66])};});
        pose.arms=[-1,1].map((side,index)=>{
            const shoulder=add(pose.neck,[side*.22,.04,.04]);
            const back=variant==='deadbug'&&index===0?-.10:0;
            return {shoulder,elbow:add(shoulder,[side*.02,.26,-.10+back]),wrist:add(shoulder,[side*.02,.54,-.16+back*2])};});
        return;
    }
    if(variant==='prone') {
        pose.prone=true;
        const lift=.10*t;
        pose.pelvis=[0,.20,.18]; pose.neck=[0,.28+lift,-.46]; pose.head=[0,.31+lift,-.62];
        pose.legs=[-1,1].map((side)=>({hip:[side*.12,.20,.20],knee:[side*.13,.22+lift*.6,.60],ankle:[side*.13,.26+lift,1.00]}));
        pose.arms=[-1,1].map((side)=>({shoulder:[side*.245,.26+lift,-.42],elbow:[side*.26,.30+lift,-.74],wrist:[side*.26,.34+lift,-1.02]}));
        return;
    }
    // Standing braced holds: pallof press and the like.
    pose.pelvis=[0,.98+sway,0]; pose.neck=add(pose.pelvis,[0,.63,0]); pose.head=add(pose.neck,[0,.15,.015]);
    pose.legs=[-1,1].map((side)=>{const hip=add(pose.pelvis,[side*.12,-.025,0]),ankle=[side*.17,.10,.02];
        return {hip,ankle,knee:joint(hip,ankle,THIGH,SHIN,[side*.2,0,1])};});
    const reach=.22+.34*t;
    pose.arms=[-1,1].map((side)=>{const shoulder=add(pose.neck,[side*.245,-.12,0]);
        const wrist=add(pose.neck,[side*.05,-.26,-reach]);
        return {shoulder,wrist,elbow:joint(shoulder,wrist,UPPER_ARM,FOREARM,[side,-.6,-.2])};});
}

/** Supine and hanging trunk work. */
function corePose(pose, motion, variant, t, phase) {
    if(motion==='legraise') {
        pose.pelvis=[0,1.02,0]; pose.neck=add(pose.pelvis,[0,.63,0]); pose.head=add(pose.neck,[0,.15,.015]);
        const raise=1.55*t;
        pose.legs=[-1,1].map((side)=>{const hip=add(pose.pelvis,[side*.12,-.025,0]);
            const knee=add(hip,[0,-THIGH*Math.cos(raise),THIGH*Math.sin(raise)]);
            return {hip,knee,ankle:add(knee,[0,-SHIN*Math.cos(raise*.85),SHIN*Math.sin(raise*.85)])};});
        pose.arms=[-1,1].map((side)=>({shoulder:add(pose.neck,[side*.245,-.10,0]),elbow:add(pose.neck,[side*.27,.22,0]),wrist:add(pose.neck,[side*.28,.52,0])}));
        return;
    }
    if(motion==='rollout') {
        pose.prone=true;
        const out=t;
        pose.pelvis=[0,.52-.20*out,.24]; pose.neck=[0,.50-.14*out,-.30-.26*out]; pose.head=[0,.50-.14*out,-.46-.28*out];
        pose.legs=[-1,1].map((side)=>({hip:add(pose.pelvis,[side*.12,0,0]),knee:[side*.13,.10,.30],ankle:[side*.13,.14,.62]}));
        pose.arms=[-1,1].map((side)=>{const shoulder=add(pose.neck,[side*.22,-.02,.02]);
            const wrist=[side*.20,.09,-.50-.46*out];
            return {shoulder,wrist,elbow:joint(shoulder,wrist,UPPER_ARM,FOREARM,[side,.2,-.6])};});
        return;
    }
    if(motion==='twist') {
        const turn=(variant==='standing'?.85:.95)*Math.sin(phase*Math.PI*2);
        const seated=variant!=='standing';
        pose.pelvis=[0,seated?.34:.98,seated?.12:0];
        pose.neck=add(pose.pelvis,[0,.60,seated?-.18:0]); pose.head=add(pose.neck,[0,.15,.015]);
        if(seated) pose.legs=[-1,1].map((side)=>({hip:add(pose.pelvis,[side*.12,0,0]),knee:[side*.17,.42,.42],ankle:[side*.18,.10,.70]}));
        else pose.legs=[-1,1].map((side)=>{const hip=add(pose.pelvis,[side*.12,-.025,0]),ankle=[side*.17,.10,.02];
            return {hip,ankle,knee:joint(hip,ankle,THIGH,SHIN,[side*.2,0,1])};});
        const height=variant==='standing'?.10+.50*(turn*.5+.5):-.18;
        pose.arms=[-1,1].map((side)=>{const shoulder=add(pose.neck,[side*.245,-.12,0]);
            const wrist=add(pose.neck,[.34*turn,height,-.34]);
            return {shoulder,wrist,elbow:joint(shoulder,wrist,UPPER_ARM,FOREARM,[side,-.4,-.3])};});
        return;
    }
    // Supine crunch variants.
    pose.horizontal=true;
    const curlUp=.24*t;
    pose.pelvis=[0,.18,.14]; pose.neck=[0,.30+curlUp,-.40+curlUp*.4]; pose.head=[0,.35+curlUp*1.3,-.52+curlUp*.5];
    const alt=Math.sin(phase*Math.PI*2);
    pose.legs=[-1,1].map((side,index)=>{const hip=add(pose.pelvis,[side*.12,0,0]);
        if(variant==='bicycle') { const drive=index?alt:-alt;
            return {hip,knee:add(hip,[0,.26+.20*drive,.24-.10*drive]),ankle:add(hip,[0,.16+.10*drive,.62+.24*drive])}; }
        if(variant==='toetouch') return {hip,knee:add(hip,[0,.40,.14]),ankle:add(hip,[0,.82,.20])};
        return {hip,knee:add(hip,[0,.30,.28]),ankle:add(hip,[0,.10,.62])};});
    pose.arms=[-1,1].map((side,index)=>{const shoulder=add(pose.neck,[side*.22,.02,.02]);
        if(variant==='toetouch') return {shoulder,elbow:add(shoulder,[side*.02,.26,.06]),wrist:add(shoulder,[side*.02,.52,.14])};
        const twist=variant==='bicycle'?(index?alt:-alt)*.10:0;
        return {shoulder,elbow:add(shoulder,[side*.20,.04,-.16+twist]),wrist:add(shoulder,[side*.12,.18,-.26+twist])};});
}

/** Repeating gait and pedal cycles. */
function cyclicPose(pose, motion, variant, t, phase) {
    if(motion==='cycle') {
        const seated=variant!=='stairs';
        pose.pelvis=[0,seated?.74:1.00,seated?.14:0];
        pose.neck=add(pose.pelvis,[0,.60,seated?-.20:0]); pose.head=add(pose.neck,[0,.15,.015]);
        pose.legs=[-1,1].map((side,index)=>{
            const angle=phase*Math.PI*2+(index?0:Math.PI);
            const hip=add(pose.pelvis,[side*.12,-.02,0]);
            const ankle=seated
                ? add(pose.pelvis,[side*.14,-.36+.22*Math.cos(angle),.40+.22*Math.sin(angle)])
                : [side*.14,.10+.26*(Math.sin(angle)*.5+.5),.10+.14*Math.cos(angle)];
            return {hip,ankle,knee:joint(hip,ankle,THIGH,SHIN,[side*.2,0,1])};});
        pose.arms=[-1,1].map((side)=>{const shoulder=add(pose.neck,[side*.245,-.12,0]);
            const wrist=seated?add(pose.neck,[side*.24,-.30,-.42]):add(pose.neck,[side*.26,-.12,-.34]);
            return {shoulder,wrist,elbow:joint(shoulder,wrist,UPPER_ARM,FOREARM,[side,-.5,-.2])};});
        return;
    }
    if(motion==='rowmachine') {
        const drive=(1-Math.cos(phase*Math.PI*2))/2;
        pose.pelvis=[0,.42,-.10+.46*drive];
        pose.neck=add(pose.pelvis,[0,.60,-.14*drive]); pose.head=add(pose.neck,[0,.15,.015]);
        pose.legs=[-1,1].map((side)=>{const hip=add(pose.pelvis,[side*.12,-.02,0]),ankle=[side*.16,.16,.72];
            return {hip,ankle,knee:joint(hip,ankle,THIGH,SHIN,[side*.2,1,.4])};});
        pose.arms=[-1,1].map((side)=>{const shoulder=add(pose.neck,[side*.245,-.12,0]);
            const wrist=add(pose.pelvis,[side*.16,.30,.62-.60*drive]);
            return {shoulder,wrist,elbow:joint(shoulder,wrist,UPPER_ARM,FOREARM,[side,-1,.3])};});
        return;
    }
    // Gait: walk, run, sprint, rope and swim share one leg cycle at different amplitudes.
    const amp=variant==='walk'?.34:variant==='sprint'?.85:variant==='rope'?.18:.65;
    const bob=(variant==='rope'?.030:.022)*Math.cos(phase*Math.PI*4);
    const lean=variant==='sprint'?.22:variant==='swim'?1.35:.04;
    pose.pelvis=[0,.98+bob,0]; pose.neck=add(pose.pelvis,[0,.63*Math.cos(lean),.63*Math.sin(lean)]);
    pose.head=add(pose.neck,[0,.15*Math.cos(lean),.15*Math.sin(lean)]);
    if(variant==='swim') pose.prone=true;
    pose.legs.forEach((leg,index)=>{
        const wave=Math.sin(phase*Math.PI*2+index*Math.PI+Math.PI/2), angle=amp*wave;
        leg.hip=add(pose.pelvis,[(index?1:-1)*.12,-.02,0]);
        leg.knee=add(leg.hip,[0,-THIGH*Math.cos(angle),THIGH*Math.sin(angle)]);
        const lower=angle-Math.max(0,-wave)*(variant==='walk'?.5:1.15);
        leg.ankle=add(leg.knee,[0,-SHIN*Math.cos(lower),SHIN*Math.sin(lower)]);
    });
    pose.arms=[-1,1].map((side,index)=>{
        const shoulder=add(pose.neck,[side*.245,-.12,0]);
        if(variant==='rope') return {shoulder,elbow:add(shoulder,[side*.10,-.26,-.06]),wrist:add(shoulder,[side*.16,-.30,-.26])};
        if(variant==='swim') { const reach=Math.sin(phase*Math.PI*2+index*Math.PI);
            return {shoulder,elbow:add(shoulder,[side*.06,.18*reach,-.24]),wrist:add(shoulder,[side*.04,.42*reach,-.48])}; }
        const angle=-(amp*.9)*Math.sin(phase*Math.PI*2+index*Math.PI+Math.PI/2);
        const elbow=add(shoulder,[0,-UPPER_ARM*Math.cos(angle),UPPER_ARM*Math.sin(angle)]);
        return {shoulder,elbow,wrist:add(elbow,[0,.08,.25])};
    });
}

/** Held stretch positions. */
function stretchPose(pose, variant, t) {
    const ease=.12*t;
    if(variant==='hamstring') {
        pose.pelvis=[0,.92,-.18]; pose.neck=add(pose.pelvis,[0,.40,.48+ease]); pose.head=add(pose.neck,[0,.10,.12]);
        pose.legs=[-1,1].map((side,index)=>{const hip=add(pose.pelvis,[side*.12,-.02,0]);
            const ankle=index?[side*.14,.10,.02]:[side*.14,.12,-.52];
            return {hip,ankle,knee:joint(hip,ankle,THIGH,SHIN,[side*.2,0,1])};});
        pose.arms=[-1,1].map((side)=>{const shoulder=add(pose.neck,[side*.22,-.08,0]);
            const wrist=add(pose.neck,[side*.16,-.30,.24]);
            return {shoulder,wrist,elbow:joint(shoulder,wrist,UPPER_ARM,FOREARM,[side,-1,.4])};});
        return;
    }
    if(variant==='hipflexor'||variant==='lunge') {
        pose.pelvis=[0,.62-ease,0]; pose.neck=add(pose.pelvis,[0,.62,variant==='lunge'?.20:0]); pose.head=add(pose.neck,[0,.15,.015]);
        pose.legs=[-1,1].map((side,index)=>{const hip=add(pose.pelvis,[side*.12,-.02,0]);
            const ankle=index?[side*.14,.085,.66]:[side*.16,.10,-.46];
            return {hip,ankle,knee:index?[side*.14,.10,.24]:joint(hip,ankle,THIGH,SHIN,[side*.2,0,1])};});
        if(variant==='lunge') pose.arms=[-1,1].map((side,index)=>{const shoulder=add(pose.neck,[side*.245,-.12,0]);
            const wrist=index?add(pose.neck,[side*.10,.34,-.20]):[side*.24,.09,-.34];
            return {shoulder,wrist,elbow:joint(shoulder,wrist,UPPER_ARM,FOREARM,[side,index?.6:-.4,.2])};});
        else pose.arms=[-1,1].map((side)=>({shoulder:add(pose.neck,[side*.245,-.12,0]),elbow:add(pose.neck,[side*.26,-.34,.04]),wrist:add(pose.neck,[side*.22,-.58,.06])}));
        return;
    }
    // Shoulder dislocate: a wide grip travelling overhead and back.
    pose.pelvis=[0,.98,0]; pose.neck=add(pose.pelvis,[0,.63,0]); pose.head=add(pose.neck,[0,.15,.015]);
    pose.legs=[-1,1].map((side)=>{const hip=add(pose.pelvis,[side*.12,-.025,0]),ankle=[side*.14,.10,.01];
        return {hip,ankle,knee:joint(hip,ankle,THIGH,SHIN,[side*.2,0,1])};});
    const sweep=Math.PI*t;
    pose.arms=[-1,1].map((side)=>{const shoulder=add(pose.neck,[side*.245,-.12,0]);
        const wrist=add(shoulder,[side*.30,.52*Math.sin(sweep)-.30*Math.cos(sweep),-.34*Math.cos(sweep)]);
        return {shoulder,wrist,elbow:joint(shoulder,wrist,UPPER_ARM,FOREARM,[side,.2,.4])};});
}

export const treadmillBeltHeight = (z) => .16+.12*z;

const cameraFrames = new Map();

/** Fit the whole motion once so the camera stays still throughout the loop. */
export function exerciseCamera(slug) {
    if (cameraFrames.has(slug)) return cameraFrames.get(slug);
    const initial = exercisePose(slug, 0);
    const horizontal = slug === 'bench-press' || initial.prone || initial.horizontal;
    const yaw = ['incline-treadmill-walk','box-jump','broad-jump','burpee','battle-ropes','calf-raise','cycling','elliptical-trainer','front-raise','hanging-leg-raise','concentration-curl','goblet-squat','kettlebell-swing','landmine-press','lat-pulldown','machine-chest-press','leg-extension','leg-press','incline-dumbbell-curl'].includes(slug) ? -1.05 : horizontal ? -1.02 : .48;
    const pitch = horizontal ? .24 : .12;
    const cy = Math.cos(yaw), sy = Math.sin(yaw), cp = Math.cos(pitch), sp = Math.sin(pitch);
    const project = ([x,y,z]) => [x*cy-z*sy, -y*cp+(x*sy+z*cy)*sp, (x*sy+z*cy)*cp+y*sp];
    const bounds = [Infinity, Infinity, -Infinity, -Infinity];
    for (let frame = 0; frame < 32; frame++) {
        const pose = exercisePose(slug, frame/32);
        const points = [pose.head, pose.neck, pose.pelvis, ...pose.arms.flatMap(Object.values), ...pose.legs.flatMap(Object.values)];
        if(slug==='incline-treadmill-walk') points.push([-.5,.02,-.85],[.5,1.44,1]);
        for(const item of exerciseEquipment(pose,slug,frame/32)) points.push(...(item.points??[item.center]));
        for (const point of points) {
            const [x,y] = project(point);
            bounds[0] = Math.min(bounds[0], x-.23);
            bounds[1] = Math.min(bounds[1], y-.23);
            bounds[2] = Math.max(bounds[2], x+.23);
            bounds[3] = Math.max(bounds[3], y+.23);
        }
    }
    const scale = Math.min(350/(bounds[2]-bounds[0]), 210/(bounds[3]-bounds[1]));
    const center = [(bounds[0]+bounds[2])/2, (bounds[1]+bounds[3])/2];
    const result = { yaw, pitch, scale, center, project };
    cameraFrames.set(slug, result);
    return result;
}

/** Draws a lit triangle mesh with a fixed camera; no runtime 3D library is required. */
export function drawExerciseDemo(canvas, slug, phase) {
    const ctx=canvas.getContext('2d'), pose=exercisePose(slug,phase);
    const horizontal=slug==='bench-press'||pose.prone||pose.horizontal;
    const {yaw, pitch, scale, center, project} = exerciseCamera(slug);
    const cy=Math.cos(yaw),sy=Math.sin(yaw),cp=Math.cos(pitch),sp=Math.sin(pitch);
    const camera=p=>{const q=project(p);return [200+(q[0]-center[0])*scale,120+(q[1]-center[1])*scale,q[2]];};
    const view=unit([sy,sp,cy]),light=unit([-.6,.85,.9]);
    const faces=[];
    const plank = slug === 'plank';
    const ellipsoid=(position,radii,color=neutral,basis=[[1,0,0],[0,1,0],[0,0,1]])=>{
        const latitude=12,longitude=18, vertices=[];
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
    for(const item of exerciseEquipment(pose,slug,phase)) {
        if(item.type==='ellipsoid') {ellipsoid(item.center,item.radii,item.color,item.basis);continue;}
        const points=item.points.map(camera);
        if(item.type==='line') {
            for(let i=1;i<points.length;i++) faces.push({points:[points[i-1],points[i]],depth:(points[i-1][2]+points[i][2])/2,line:true,stroke:item.color,width:item.width});
        } else faces.push({points,depth:item.role==='ground'?-Infinity:points.reduce((sum,p)=>sum+p[2],0)/points.length,fill:item.color,stroke:'#54738d'});
    }
    if(slug==='incline-treadmill-walk') {
        const panel=(points,fill,stroke='#344f68')=>{
            const projected=points.map(camera);
            faces.push({points:projected,depth:projected.reduce((sum,p)=>sum+p[2],0)/projected.length,fill,stroke});
        };
        const belt=(x,z)=>[x,treadmillBeltHeight(z),z];
        panel([belt(-.43,-.85),belt(.43,-.85),belt(.43,.9),belt(-.43,.9)],'#253749');
        for(const side of [-1,1]) {
            const x=side*.43;
            panel([belt(x,-.85),belt(x,.9),[x,.03,.9],[x,.03,-.85]],'#172432');
            bone([side*.40,treadmillBeltHeight(.78),.78],[side*.40,1.27,.9],.027,.027,[83,111,134]);
            bone([side*.40,1.12,.82],[side*.40,1.08,.13],.025,.025,[83,111,134]);
        }
        panel([belt(-.43,-.85),belt(.43,-.85),[.43,.03,-.85],[-.43,.03,-.85]],'#1c2b3b');
        panel([belt(-.34,-.79),belt(.34,-.79),belt(.34,.80),belt(-.34,.80)],'#101d29');
        for(let mark=0;mark<12;mark++) {
            const z=-.78+((mark/12-phase*.5+1)%1)*1.56;
            panel([[-.33,treadmillBeltHeight(z)+.004,z],[.33,treadmillBeltHeight(z)+.004,z],[.33,treadmillBeltHeight(z+.012)+.004,z+.012],[-.33,treadmillBeltHeight(z+.012)+.004,z+.012]],'#354b5c','#354b5c');
        }
        panel([[-.48,1.27,.82],[.48,1.27,.82],[.48,1.44,1],[-.48,1.44,1]],'#253b50');
        panel([[-.20,1.31,.85],[.20,1.31,.85],[.20,1.40,.95],[-.20,1.40,.95]],'#416e8b','#76b5d6');
    }
    const up=unit(sub(pose.neck,pose.pelvis));
    const right=unit(sub(pose.right??[1,0,0],mul(up,dot(pose.right??[1,0,0],up))));
    const forward=mul(unit(cross(right,up)),pose.prone?-1:1),basis=[right,up,forward];
    const body=(height,x,depth)=>add(add(mix(pose.pelvis,pose.neck,height),[0,(pose.spineArch??0)*Math.sin(Math.PI*height),0]),add(mul(right,x),mul(forward,depth)));
    // Hand-authored slugs keep their original tinting; archetypes derive it.
    const focus=focusFor(slug);
    const chestFocus=['bench-press','push-up'].includes(slug)||focus==='chest'||focus==='arms';
    const legsFocus=slug==='bodyweight-squat'||focus==='legs';
    const coreFocus=slug==='plank'||focus==='core'||focus==='back';
    {
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
                const highlighted=(coreFocus && ring>=2 && ring<=5) || (chestFocus && ring>=5 && ring<=8);
                const color=highlighted?blue:neutral;
                const shade=.34+.66*Math.max(0,dot(normal,light));
                faces.push({points:points.map(vertex=>vertex.point),depth:points.reduce((sum,vertex)=>sum+vertex.point[2],0)/4,fill:`rgb(${color.map(value=>Math.round(value*shade)).join(',')})`});
            }
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
        ellipsoid(arm.elbow,[.044,.046,.043]);
        bone(arm.elbow,arm.wrist,.047,.039);
        const handAxis=unit(sub(arm.wrist,arm.elbow)),handEnd=add(arm.wrist,mul(handAxis,.085));
        if ((pose.palms || ['bird-dog','cat-cow','clap-push-up','close-grip-push-up'].includes(slug)) && (slug!=='clap-push-up' || arm.wrist[1]<.12)) {
            bone(arm.wrist,add(arm.wrist,[0,-.025,-.09]),.033,.018);
        } else if(slug==='clap-push-up') {
            ellipsoid(arm.wrist,[.018,.05,.03]);
        } else if (['bench-press','dumbbell-curl','incline-treadmill-walk','ab-wheel-rollout','battle-ropes'].includes(slug) || (FORM_DEMOS.includes(slug) && !['dead-bug','cat-cow'].includes(slug)) || loadFor(slug)) {
            ellipsoid(arm.wrist,[.043,.039,.038]);
            ellipsoid(add(arm.wrist,[.027,.017,.012]),[.014,.025,.018]);
        } else {
            bone(arm.wrist,handEnd,.034,.025);
            for(let finger=0;finger<4;finger++) {
                const at=add(handEnd,[(finger-1.5)*.014,0,0]);bone(at,add(at,mul(handAxis,.037)),.007,.008);
            }
        }
        if(!AUTHORED_LOADS.includes(slug) && (slug==='dumbbell-curl'||loadFor(slug)==='dumbbell')) {
            const a=add(arm.wrist,[-.095,0,0]),b=add(arm.wrist,[.095,0,0]);bone(a,b,.015,.015,[150,168,190]);
            for(const point of [a,b])ellipsoid(point,[.027,.068,.068],[77,92,119]);
        }
    });
    // A barbell spans both hands, so it is drawn once rather than per arm.
    if(!AUTHORED_LOADS.includes(slug) && loadFor(slug)==='barbell' && pose.arms.length===2) {
        const [left,right]=pose.arms.map((arm)=>arm.wrist);
        const axis=unit(sub(right,left)), outer=.30;
        const a=sub(left,mul(axis,outer)), b=add(right,mul(axis,outer));
        bone(a,b,.014,.014,[188,202,222]);
        for(const end of [add(a,mul(axis,.10)),sub(b,mul(axis,.10))]) {
            ellipsoid(end,[.034,.132,.132],[59,75,101]);
        }
    }
    pose.legs.forEach((leg)=>{
        bone(leg.hip,leg.knee,.092,.097,legsFocus?blue:neutral);
        ellipsoid(leg.knee,[.058,.059,.061]);
        bone(leg.knee,leg.ankle,.059,.053);
        ellipsoid(leg.ankle,[.036,.038,.042]);
        if(slug==='calf-raise') {
            bone(leg.ankle,[leg.ankle[0],.04,.12],.048,.025);
        } else if (plank) {
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
    for(const face of faces) {ctx.beginPath();face.points.forEach((p,i)=>i?ctx.lineTo(p[0],p[1]):ctx.moveTo(p[0],p[1]));if(!face.line){ctx.closePath();ctx.fillStyle=face.fill;ctx.fill();}ctx.strokeStyle=face.stroke??'rgba(115,193,245,.20)';ctx.lineWidth=face.width??.4;ctx.lineCap='round';ctx.stroke();}
}
