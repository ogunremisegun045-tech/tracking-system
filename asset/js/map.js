let map = null;
let marker = null;

function trackOrder() {
    const id = document.getElementById('trackingInput').value.trim();
    if (!id) {
        showError('Please enter a tracking ID.');
        return;
    }
    fetchTracking(id);
}

function quickTrack(trackingId) {
    document.getElementById('trackingInput').value = trackingId;
    fetchTracking(trackingId);
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function fetchTracking(trackingId) {
    const resultBox = document.getElementById('trackResult');
    const errorBox  = document.getElementById('trackError');

    resultBox.classList.add('hidden');
    errorBox.classList.add('hidden');

    var url = '/tracking-system/api/get_tracking.php?tracking_id=' + encodeURIComponent(trackingId);

    fetch(url)
        .then(function(res) { return res.json(); })
        .then(function(data) {
            errorBox.classList.add('hidden');

            if (data.error) {
                showError('You must be logged in to track orders.');
                return;
            }
            if (data.status === 'Not Found') {
                showError('No order found with that tracking ID.');
                return;
            }

            document.getElementById('resultStatus').textContent   = data.status;
            document.getElementById('resultLocation').textContent = data.location;
            resultBox.classList.remove('hidden');
            startAutoRefresh(trackingId);

            var lat = parseFloat(data.latitude);
            var lng = parseFloat(data.longitude);

            if (!map) {
                map = L.map('map').setView([lat, lng], 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '© OpenStreetMap contributors'
                }).addTo(map);
            } else {
                map.setView([lat, lng], 13);
            }

            if (marker) {
                marker.setLatLng([lat, lng]);
            } else {
                marker = L.marker([lat, lng]).addTo(map);
            }

            marker.bindPopup('<b>' + data.status + '</b><br>' + data.location).openPopup();
        })
        .catch(function() { showError('Something went wrong. Please try again.'); });
}

function showError(msg) {
    const errorBox = document.getElementById('trackError');
    errorBox.textContent = msg;
    errorBox.classList.remove('hidden');
}
var autoRefreshInterval = null;
var currentTrackingId = null;

function startAutoRefresh(trackingId) {
    currentTrackingId = trackingId;
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
    }
    autoRefreshInterval = setInterval(function() {
        fetchTracking(currentTrackingId);
    }, 30000);
}

function stopAutoRefresh() {
    if (autoRefreshInterval) {
        clearInterval(autoRefreshInterval);
        autoRefreshInterval = null;
    }
}