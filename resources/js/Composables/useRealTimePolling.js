import { ref, onMounted, onUnmounted, watch } from 'vue';

/**
 * Composable for real-time polling with automatic cleanup and error handling
 * 
 * @param {string} endpoint - The endpoint URL to poll
 * @param {Object} options - Configuration options
 * @param {number} options.interval - Polling interval in milliseconds (default: 30000)
 * @param {boolean} options.enabled - Whether polling is enabled (default: true)
 * @param {Function} options.onSuccess - Callback when polling succeeds
 * @param {Function} options.onError - Callback when polling fails
 * @param {Function} options.transform - Transform function for response data
 * @param {boolean} options.pauseOnHidden - Pause polling when tab is hidden (default: true)
 * @returns {Object} - Polling state and controls
 */
export function useRealTimePolling(endpoint, options = {}) {
    const {
        interval = 30000,
        enabled = true,
        onSuccess = null,
        onError = null,
        transform = null,
        pauseOnHidden = true,
    } = options;

    const data = ref(null);
    const loading = ref(false);
    const error = ref(null);
    const isPolling = ref(false);
    const lastUpdated = ref(null);
    const pollInterval = ref(null);
    const retryCount = ref(0);
    const maxRetries = 3;
    const baseRetryDelay = 1000; // 1 second

    // Page visibility handling
    const isPageVisible = ref(!document.hidden);

    const handleVisibilityChange = () => {
        isPageVisible.value = !document.hidden;
        if (pauseOnHidden) {
            if (isPageVisible.value && enabled && !pollInterval.value) {
                startPolling();
            } else if (!isPageVisible.value && pollInterval.value) {
                stopPolling();
            }
        }
    };

    const fetchData = async () => {
        if (!enabled || (pauseOnHidden && !isPageVisible.value)) {
            return;
        }

        loading.value = true;
        error.value = null;

        try {
            const response = await fetch(endpoint, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const responseData = await response.json();
            const transformedData = transform ? transform(responseData) : responseData;
            
            data.value = transformedData;
            lastUpdated.value = new Date();
            retryCount.value = 0; // Reset retry count on success

            if (onSuccess) {
                onSuccess(transformedData);
            }
        } catch (err) {
            error.value = err;
            retryCount.value += 1;

            if (onError) {
                onError(err);
            }

            // Exponential backoff on error
            if (retryCount.value < maxRetries) {
                const retryDelay = baseRetryDelay * Math.pow(2, retryCount.value - 1);
                setTimeout(() => {
                    if (isPolling.value) {
                        fetchData();
                    }
                }, retryDelay);
            }
        } finally {
            loading.value = false;
        }
    };

    const startPolling = () => {
        if (pollInterval.value || !enabled) {
            return;
        }

        isPolling.value = true;
        
        // Fetch immediately
        fetchData();
        
        // Then poll at interval
        pollInterval.value = setInterval(() => {
            if (isPageVisible.value || !pauseOnHidden) {
                fetchData();
            }
        }, interval);
    };

    const stopPolling = () => {
        if (pollInterval.value) {
            clearInterval(pollInterval.value);
            pollInterval.value = null;
        }
        isPolling.value = false;
    };

    const refresh = () => {
        fetchData();
    };

    onMounted(() => {
        if (enabled) {
            startPolling();
        }
        
        if (pauseOnHidden) {
            document.addEventListener('visibilitychange', handleVisibilityChange);
        }
    });

    onUnmounted(() => {
        stopPolling();
        if (pauseOnHidden) {
            document.removeEventListener('visibilitychange', handleVisibilityChange);
        }
    });

    // Watch for enabled changes
    watch(() => enabled, (newValue) => {
        if (newValue && !pollInterval.value) {
            startPolling();
        } else if (!newValue && pollInterval.value) {
            stopPolling();
        }
    });

    return {
        data,
        loading,
        error,
        isPolling,
        lastUpdated,
        refresh,
        startPolling,
        stopPolling,
    };
}

