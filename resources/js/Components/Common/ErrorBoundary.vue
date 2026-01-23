<script>
export default {
  name: 'ErrorBoundary',
  data() {
    return { error: null };
  },
  errorCaptured(err) {
    this.error = err;
    try {
      window.__toast?.add({ title: 'Error', message: String(err?.message || err), type: 'error', duration: 5000 });
    } catch {}
    return false;
  },
  render() {
    if (this.error) {
      return this.$slots.fallback ? this.$slots.fallback() : null;
    }
    return this.$slots.default ? this.$slots.default() : null;
  }
}
</script>
