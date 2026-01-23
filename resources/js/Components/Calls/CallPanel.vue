<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import Echo from '@/Utils/echo';
import { initTwilioRoom } from '@/Services/sfu/twilio';

const props = defineProps({
  conversationId: String,
  iceServers: { type: Array, default: () => [{ urls: ['stun:stun.l.google.com:19302'] }] },
  currentUserId: [String, Number],
});

const localVideoRef = ref(null);
const remoteVideos = ref([]);
const peers = {};
let localStream = null;
const activeSessionId = ref(null);
const muted = ref(false);
const videoEnabled = ref(true);
const inRoom = ref(false);
let metricsTimer = null;
const activeSpeakerUserId = ref(null);
const analysers = {};
const selectedPermissionUserId = ref('');
let recorder = null;
let recordedChunks = [];
const isRecording = ref(false);
const isPaused = ref(false);
let sfuRoom = null;
const sfuParticipants = ref([]);
const sfuContainerRef = ref(null);

const startLocal = async () => {
  localStream = await navigator.mediaDevices.getUserMedia({ audio: true, video: { width: 1280, height: 720 } });
  localVideoRef.value.srcObject = localStream;
};

const createPeer = (userId) => {
  const pc = new RTCPeerConnection({ iceServers: props.iceServers });
  localStream.getTracks().forEach(track => pc.addTrack(track, localStream));
  pc.ontrack = (ev) => {
    const stream = ev.streams[0];
    remoteVideos.value = remoteVideos.value.filter(v => v.userId !== userId).concat([{ userId, stream }]);
    try {
      const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
      const source = audioCtx.createMediaStreamSource(stream);
      const analyser = audioCtx.createAnalyser();
      analyser.fftSize = 512;
      source.connect(analyser);
      analysers[userId] = { analyser, audioCtx };
    } catch {}
  };
  pc.onicecandidate = (ev) => {
    if (ev.candidate) {
      sendSignal('ice', { candidate: ev.candidate, target_user_id: userId });
    }
  };
  peers[userId] = pc;
  return pc;
};

const sendSignal = async (type, data) => {
  const payload = { type, data };
  await fetch(route('calls.signal', props.conversationId), {
    method: 'POST',
    credentials: 'include',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
    body: JSON.stringify(payload),
  });
};

const handleSignal = async (e) => {
  const { sender, payload } = e;
  const fromId = sender?.id;
  if (!fromId) return;
  if (!peers[fromId]) createPeer(fromId);
  const pc = peers[fromId];
  if (payload.type === 'offer') {
    await pc.setRemoteDescription(payload.data);
    const answer = await pc.createAnswer();
    await pc.setLocalDescription(answer);
    sendSignal('answer', { sdp: answer, target_user_id: fromId });
  } else if (payload.type === 'answer') {
    await pc.setRemoteDescription(payload.data.sdp);
  } else if (payload.type === 'ice') {
    try { await pc.addIceCandidate(payload.data.candidate); } catch {}
  } else if (payload.type === 'host.mute_all') {
    toggleMute(true);
  } else if (payload.type === 'host.kick' && payload.data?.user_id) {
    if (String(payload.data.user_id) === String(props.currentUserId)) endCall();
  } else if (payload.type === 'host.permission' && payload.data?.user_id) {
    if (String(payload.data.user_id) === String(props.currentUserId)) {
      window.__toast?.add({ title: 'Permission Changed', message: `Screen share ${payload.data.can_share_screen ? 'enabled' : 'disabled'}`, type: 'success' });
    }
  }
};

const callOneOnOne = async (targetUserId) => {
  const pc = createPeer(targetUserId);
  const offer = await pc.createOffer({ offerToReceiveAudio: true, offerToReceiveVideo: true });
  await pc.setLocalDescription(offer);
  await sendSignal('offer', { sdp: offer, target_user_id: targetUserId });
};

const screenShare = async () => {
  if (!isHost.value) {
    window.__toast?.add({ title: 'Permission', message: 'Screen share requires host approval', type: 'error' });
    return;
  }
  const display = await navigator.mediaDevices.getDisplayMedia({ video: true });
  const screenTrack = display.getVideoTracks()[0];
  Object.values(peers).forEach(pc => {
    const sender = pc.getSenders().find(s => s.track.kind === 'video');
    if (sender) sender.replaceTrack(screenTrack);
  });
};

const toggleMute = (forceMute = null) => {
  const next = forceMute === null ? !muted.value : !!forceMute;
  muted.value = next;
  localStream.getAudioTracks().forEach(t => t.enabled = !next);
};

const toggleVideo = () => {
  videoEnabled.value = !videoEnabled.value;
  localStream.getVideoTracks().forEach(t => t.enabled = videoEnabled.value);
};

const fetchTwilioToken = async () => {
  const res = await fetch(route('rtc.sfu.token'), {
    method: 'POST',
    credentials: 'include',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
    body: JSON.stringify({ room: `conv-${props.conversationId}` }),
  });
  return res.json();
};

const joinSFURoom = async () => {
  try {
    const { room } = await initTwilioRoom(fetchTwilioToken, localStream);
    sfuRoom = room;
    // mount local tracks
    sfuRoom.localParticipant.tracks.forEach(pub => {
      const track = pub.track;
      if (track) {
        const el = track.attach();
        el.setAttribute('data-local', 'true');
        if (sfuContainerRef.value) sfuContainerRef.value.appendChild(el);
      }
    });
    sfuRoom.on('participantConnected', participant => {
      sfuParticipants.value = sfuParticipants.value.concat([participant]);
      participant.on('trackSubscribed', track => {
        if (track.kind === 'video' || track.kind === 'audio') {
          const el = track.attach();
          if (sfuContainerRef.value) {
            const wrapper = document.createElement('div');
            wrapper.setAttribute('data-sid', participant.sid);
            wrapper.className = 'relative overflow-hidden rounded bg-black';
            wrapper.appendChild(el);
            sfuContainerRef.value.appendChild(wrapper);
            highlightTwilioSpeaker();
          }
        }
      });
    });
    sfuRoom.on('participantDisconnected', participant => {
      sfuParticipants.value = sfuParticipants.value.filter(p => p.sid !== participant.sid);
      // remove elements
      const children = Array.from(sfuContainerRef.value?.children || []);
      children.forEach(el => {
        if (el.getAttribute('data-sid') === participant.sid) el.remove();
      });
    });
    sfuRoom.on('trackUnsubscribed', track => {
      try { track.detach().forEach(el => el.remove()); } catch {}
    });
    sfuRoom.on('dominantSpeakerChanged', evt => {
      activeTwilioSpeakerSid.value = evt?.participant?.sid || null;
      highlightTwilioSpeaker();
    });
    window.__toast?.add({ title: 'SFU', message: 'Joined SFU room', type: 'success' });
  } catch (e) {
    window.__toast?.add({ title: 'SFU Error', message: String(e?.message || e), type: 'error' });
  }
};

const startRecording = async (format = 'webm') => {
  if (!localStream) return;
  const mime = format === 'mp4' ? 'video/mp4' : 'video/webm';
  try {
    recorder = new MediaRecorder(localStream, { mimeType: mime });
  } catch {
    recorder = new MediaRecorder(localStream);
  }
  recordedChunks = [];
  recorder.ondataavailable = (e) => {
    if (e.data && e.data.size > 0) recordedChunks.push(e.data);
  };
  recorder.onstop = uploadRecording;
  recorder.start(1000);
  isRecording.value = true;
  isPaused.value = false;
};

const pauseRecording = () => {
  if (recorder && recorder.state === 'recording') {
    recorder.pause();
    isPaused.value = true;
  }
};

const resumeRecording = () => {
  if (recorder && recorder.state === 'paused') {
    recorder.resume();
    isPaused.value = false;
  }
};

const stopRecording = () => {
  if (recorder && (recorder.state === 'recording' || recorder.state === 'paused')) {
    recorder.stop();
    isRecording.value = false;
    isPaused.value = false;
  }
};

const uploadRecording = async () => {
  const blob = new Blob(recordedChunks, { type: recordedChunks[0]?.type || 'video/webm' });
  const fd = new FormData();
  fd.append('recording', new File([blob], `call_${Date.now()}.webm`, { type: blob.type }));
  const url = route('calls.recordings', { conversation: props.conversationId, session: activeSessionId.value });
  await fetch(url, { method: 'POST', credentials: 'include', body: fd });
  window.__toast?.add({ title: 'Recording', message: 'Upload completed', type: 'success' });
};
const endCall = () => {
  Object.values(peers).forEach(pc => {
    try { pc.close(); } catch {}
  });
  remoteVideos.value = [];
  inRoom.value = false;
};

const fetchActiveSession = async () => {
  const res = await fetch(route('calls.active', props.conversationId), { credentials: 'include', headers: { 'Accept': 'application/json' } });
  if (!res.ok) return;
  const data = await res.json();
  activeSessionId.value = data.session?.id || null;
  hostUserId.value = data.session?.host_user_id || null;
  window.__currentCallSessionId = activeSessionId.value;
};

const joinRoom = async () => {
  await fetchActiveSession();
  if (!activeSessionId.value) return;
  const url = route('calls.join', { conversation: props.conversationId, session: activeSessionId.value });
  await fetch(url, { method: 'POST', credentials: 'include', headers: { 'Accept': 'application/json' } });
  inRoom.value = true;
};

const leaveRoom = async () => {
  if (!activeSessionId.value) return;
  const url = route('calls.leave', { conversation: props.conversationId, session: activeSessionId.value });
  await fetch(url, { method: 'POST', credentials: 'include', headers: { 'Accept': 'application/json' } });
  endCall();
};

const hostMuteAll = async () => {
  if (!activeSessionId.value) return;
  const url = route('calls.mute_all', { conversation: props.conversationId, session: activeSessionId.value });
  await fetch(url, { method: 'POST', credentials: 'include', headers: { 'Accept': 'application/json' } });
};

const hostUserId = ref(null);
const isHost = computed(() => hostUserId.value && String(hostUserId.value) === String(props.currentUserId));

onMounted(async () => {
  await startLocal();
  window.Echo?.private(`conversation.${props.conversationId}`)
    .listen('.call.signal', (e) => handleSignal(e))
    .listen('.call.participant.joined', (e) => {
      window.__toast?.add({ title: 'Participant Joined', message: `${e.user?.name || 'User'} joined`, type: 'success' });
    })
    .listen('.call.participant.left', (e) => {
      window.__toast?.add({ title: 'Participant Left', message: `${e.user?.name || 'User'} left`, type: 'error' });
    });
  await fetchActiveSession();
});

const startMetrics = () => {
  if (metricsTimer) clearInterval(metricsTimer);
  metricsTimer = setInterval(async () => {
    const pcs = Object.values(peers);
    if (pcs.length === 0 || !activeSessionId.value || !inRoom.value) return;
    try {
      const pc = pcs[0];
      const stats = await pc.getStats();
      let rtt = null;
      let lossPct = null;
      let jitter = null;
      let outboundVideoSender = null;
      stats.forEach(report => {
        if (report.type === 'candidate-pair' && report.currentRoundTripTime) {
          rtt = Math.round(report.currentRoundTripTime * 1000);
        }
        if (report.type === 'inbound-rtp' && typeof report.packetsLost === 'number' && typeof report.packetsReceived === 'number') {
          const total = report.packetsLost + report.packetsReceived;
          if (total > 0) lossPct = Math.round((report.packetsLost / total) * 10000) / 100;
          if (typeof report.jitter === 'number') jitter = Math.round(report.jitter * 1000);
        }
        if (report.type === 'outbound-rtp' && report.kind === 'video') {
          outboundVideoSender = report;
        }
      });
      const url = route('calls.metrics', { conversation: props.conversationId, session: activeSessionId.value });
      await fetch(url, {
        method: 'POST',
        credentials: 'include',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({ latency_ms: rtt, packet_loss_pct: lossPct, jitter_ms: jitter }),
      });
      if (outboundVideoSender) {
        const senders = pc.getSenders().filter(s => s.track && s.track.kind === 'video');
        if (senders.length > 0) {
          const sender = senders[0];
          const params = sender.getParameters();
          const enc = params.encodings && params.encodings[0] ? params.encodings[0] : (params.encodings = [{}], params.encodings[0]);
          if (lossPct !== null && lossPct > 2) enc.maxBitrate = 250000;
          else if (rtt !== null && rtt > 800) enc.maxBitrate = 300000;
          else enc.maxBitrate = 800000;
          await sender.setParameters(params);
        }
      }
    } catch {}
  }, 10000);
};

watch(inRoom, (v) => {
  if (v) startMetrics(); else if (metricsTimer) clearInterval(metricsTimer);
});

const updateActiveSpeaker = () => {
  let bestUser = null;
  let bestVal = -1;
  Object.entries(analysers).forEach(([uid, obj]) => {
    const buf = new Uint8Array(obj.analyser.frequencyBinCount);
    obj.analyser.getByteFrequencyData(buf);
    const avg = buf.reduce((a, b) => a + b, 0) / buf.length;
    if (avg > bestVal) {
      bestVal = avg;
      bestUser = uid;
    }
  });
  activeSpeakerUserId.value = bestUser;
  requestAnimationFrame(updateActiveSpeaker);
};
requestAnimationFrame(updateActiveSpeaker);

const setScreenPermissionForSelected = (uid) => {
  selectedPermissionUserId.value = uid;
};

const updateSelectedPermission = async (allow) => {
  if (!activeSessionId.value || !selectedPermissionUserId.value) return;
  const url = route('calls.permissions', { conversation: props.conversationId, session: activeSessionId.value, userId: selectedPermissionUserId.value });
  await fetch(url, {
    method: 'POST',
    credentials: 'include',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
    body: JSON.stringify({ can_share_screen: !!allow }),
  });
}

const confirmKick = async () => {
  if (!activeSessionId.value || !selectedPermissionUserId.value) return;
  const ok = window.confirm('Kick selected participant?');
  if (!ok) return;
  const url = route('calls.kick', { conversation: props.conversationId, session: activeSessionId.value, userId: selectedPermissionUserId.value });
  await fetch(url, { method: 'POST', credentials: 'include', headers: { 'Accept': 'application/json' } });
}

// Twilio dominant speaker sync
const activeTwilioSpeakerSid = ref(null);
const highlightTwilioSpeaker = () => {
  const children = sfuContainerRef.value?.children || [];
  for (const el of children) {
    const sid = el.getAttribute('data-sid');
    if (sid && sid === activeTwilioSpeakerSid.value) {
      el.classList.add('ring-4', 'ring-green-500', 'scale-[1.03]', 'transition-all', 'duration-300');
    } else {
      el.classList.remove('ring-4', 'ring-green-500', 'scale-[1.03]');
    }
  }
};
</script>

<template>
  <div class="space-y-2">
    <div class="flex gap-2">
      <video ref="localVideoRef" class="w-64 h-36 bg-black" autoplay :muted="muted" playsinline></video>
      <div class="grid grid-cols-2 gap-2">
        <video v-for="rv in remoteVideos" :key="rv.userId" :class="['w-64 h-36 bg-black transition-all duration-300', String(rv.userId) === String(activeSpeakerUserId) ? 'ring-4 ring-green-500 scale-[1.03] will-change-transform' : '']" autoplay playsinline :srcObject="rv.stream"></video>
      </div>
      <div v-if="sfuParticipants.length" class="mt-2">
        <div class="text-sm font-semibold">SFU Participants: {{ sfuParticipants.length }}</div>
        <div ref="sfuContainerRef" class="grid grid-cols-3 gap-2 mt-2"></div>
      </div>
    </div>
    <div class="flex gap-2">
      <button class="px-2 py-1 rounded text-sm" :class="muted ? 'bg-gray-500 text-white' : 'bg-green-600 text-white'" @click="toggleMute">{{ muted ? 'Unmute' : 'Mute' }}</button>
      <button class="px-2 py-1 rounded text-sm" :class="videoEnabled ? 'bg-green-600 text-white' : 'bg-gray-500 text-white'" @click="toggleVideo">{{ videoEnabled ? 'Video On' : 'Video Off' }}</button>
      <button class="px-2 py-1 bg-indigo-600 text-white rounded text-sm" @click="screenShare">Screen Share</button>
      <button class="px-2 py-1 bg-blue-700 text-white rounded text-sm" @click="joinSFURoom">Join SFU Room</button>
      <button class="px-2 py-1 bg-red-600 text-white rounded text-sm" @click="endCall">End Call</button>
      <button class="px-2 py-1 bg-teal-600 text-white rounded text-sm" v-if="!isRecording" @click="startRecording('webm')">Start Recording</button>
      <button class="px-2 py-1 bg-yellow-600 text-white rounded text-sm" v-if="isRecording && !isPaused" @click="pauseRecording">Pause</button>
      <button class="px-2 py-1 bg-green-600 text-white rounded text-sm" v-if="isRecording && isPaused" @click="resumeRecording">Resume</button>
      <button class="px-2 py-1 bg-gray-600 text-white rounded text-sm" v-if="isRecording" @click="stopRecording">Stop</button>
      <button class="px-2 py-1 rounded text-sm" :class="inRoom ? 'bg-yellow-600 text-white' : 'bg-blue-600 text-white'" @click="inRoom ? leaveRoom() : joinRoom()">{{ inRoom ? 'Leave Room' : 'Join Room' }}</button>
      <button v-if="isHost" class="px-2 py-1 bg-purple-600 text-white rounded text-sm" @click="hostMuteAll">Mute All</button>
      <button v-if="isHost" class="px-2 py-1 bg-red-700 text-white rounded text-sm" @click="confirmKick()">Kick Selected</button>
      <div v-if="isHost" class="flex items-center gap-2">
        <label class="text-sm">Permissions:</label>
        <select class="border rounded text-sm" @change="e => setScreenPermissionForSelected(e.target.value)">
          <option value="">Select User</option>
          <option v-for="rv in remoteVideos" :key="rv.userId" :value="rv.userId">User {{ rv.userId }}</option>
        </select>
        <button class="px-2 py-1 bg-gray-700 text-white rounded text-sm" @click="updateSelectedPermission(true)">Allow Share</button>
        <button class="px-2 py-1 bg-gray-700 text-white rounded text-sm" @click="updateSelectedPermission(false)">Disable Share</button>
      </div>
    </div>
  </div>
</template>
