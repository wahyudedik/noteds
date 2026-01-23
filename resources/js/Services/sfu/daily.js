export async function initDailyRoom(fetchToken) {
  const { token } = await fetchToken();
  return { room: null, token };
}
