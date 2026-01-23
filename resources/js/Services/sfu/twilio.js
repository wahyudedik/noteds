import Video, { LocalAudioTrack, LocalVideoTrack } from 'twilio-video';

export async function initTwilioRoom(fetchToken, localStream) {
  const { token, room } = await fetchToken();
  let tracks = [];
  if (localStream) {
    const audio = localStream.getAudioTracks()[0];
    const video = localStream.getVideoTracks()[0];
    if (audio) tracks.push(new LocalAudioTrack(audio));
    if (video) tracks.push(new LocalVideoTrack(video));
  } else {
    const created = await Video.createLocalTracks({ audio: true, video: true });
    tracks = created;
  }
  const twilioRoom = await Video.connect(token, {
    name: room,
    tracks,
    dominantSpeaker: true,
    bandwidthProfile: { video: { mode: 'collaboration' } },
  });
  return { room: twilioRoom };
}
