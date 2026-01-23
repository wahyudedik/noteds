export async function initAgoraSession(fetchToken) {
  const { token } = await fetchToken();
  return { session: null, token };
}
