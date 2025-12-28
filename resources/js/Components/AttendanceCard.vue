<script setup>
import { onMounted, ref, onUnmounted, computed, watch } from 'vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { 
    MapPin, 
    Camera, 
    Fingerprint, 
    AlertCircle, 
    RefreshCw,
    Video,
    VideoOff,
    Building2,
    Home
} from 'lucide-vue-next';

const props = defineProps({
    officeCoordinates: {
        type: Array, // [lat, lng]
        default: () => [-7.3172337, 112.7888917] // PT Inosoft Trans Sistem
    },
    officeName: {
        type: String,
        default: 'PT Inosoft Trans Sistem'
    },
    maxDistance: {
        type: Number,
        default: 100 // meters
    }
});

// -- State --
const attendanceMode = ref('WFO'); // 'WFO' | 'WFH'
const mapContainer = ref(null);
const map = ref(null);
const userMarker = ref(null);
const videoElement = ref(null);
const capturedImage = ref(null);
const stream = ref(null);
const userLocation = ref(null);
const distanceFromOffice = ref(null); // in meters
const isInRange = ref(false);
const isLoadingLocation = ref(true);
const cameraError = ref(null);
const locationError = ref(null);
const currentTime = ref('00:00:00');
const countdown = ref('');
const isCameraActive = ref(true);

// -- Constants --
const EARTH_RADIUS_KM = 6371;

// -- Timer Logic --
const updateTime = () => {
    const now = new Date();
    currentTime.value = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
    
    // Countdown to 09:00
    const deadline = new Date();
    deadline.setHours(9, 0, 0, 0);
    
    if (now > deadline) {
        countdown.value = 'Terlambat';
    } else {
        const diff = deadline - now;
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((diff % (1000 * 60)) / 1000);
        countdown.value = `-${hours}j ${minutes}m ${seconds}s`;
    }
};
let timerInterval;

// -- Geolocation Logic --
const calculateDistance = (lat1, lon1, lat2, lon2) => {
    const dLat = (lat2 - lat1) * Math.PI / 180;
    const dLon = (lon2 - lon1) * Math.PI / 180;
    const a = 
        Math.sin(dLat/2) * Math.sin(dLat/2) +
        Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) * 
        Math.sin(dLon/2) * Math.sin(dLon/2); 
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a)); 
    const d = EARTH_RADIUS_KM * c * 1000; // Distance in meters
    return Math.round(d);
};

const watchLocation = () => {
    if (!navigator.geolocation) {
        locationError.value = "Geolocation tidak didukung browser ini.";
        return;
    }

    navigator.geolocation.watchPosition(
        (position) => {
            isLoadingLocation.value = false;
            const { latitude, longitude } = position.coords;
            userLocation.value = [latitude, longitude];
            
            // Calculate Distance
            const dist = calculateDistance(
                latitude, 
                longitude, 
                props.officeCoordinates[0], 
                props.officeCoordinates[1]
            );
            distanceFromOffice.value = dist;
            isInRange.value = dist <= props.maxDistance;

            // Update Map Marker
            if (map.value && userMarker.value) {
                userMarker.value.setLatLng([latitude, longitude]);
            } else if (map.value && !userMarker.value) {
                const customIcon = L.divIcon({
                    className: 'custom-div-icon',
                    html: `<div style="background-color: #3b82f6; width: 12px; height: 12px; border-radius: 50%; border: 2px solid white; box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.3);"></div>`,
                    iconSize: [20, 20],
                    iconAnchor: [10, 10]
                });
                
                userMarker.value = L.marker([latitude, longitude], { icon: customIcon }).addTo(map.value);
                map.value.setView([latitude, longitude], 16);
            }
        },
        (error) => {
            isLoadingLocation.value = false;
            locationError.value = "Gagal mendapatkan lokasi: " + error.message;
        },
        { enableHighAccuracy: true, maximumAge: 10000, timeout: 5000 }
    );
};

// -- Map Initialization --
const initMap = () => {
    if (mapContainer.value) {
        map.value = L.map(mapContainer.value, { zoomControl: false }).setView(props.officeCoordinates, 15);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map.value);

        // Office Perimeter Circle
        L.circle(props.officeCoordinates, {
            color: '#0b5cad',
            fillColor: '#0b5cad',
            fillOpacity: 0.1,
            radius: props.maxDistance
        }).addTo(map.value);
        
        // Office Marker
        L.marker(props.officeCoordinates).addTo(map.value)
            .bindPopup(props.officeName);
    }
};

// -- Camera Logic --
const stopCamera = () => {
    if (stream.value) {
        stream.value.getTracks().forEach(track => track.stop());
        stream.value = null;
    }
    if (videoElement.value) {
        videoElement.value.srcObject = null;
    }
    isCameraActive.value = false;
};

const startCamera = async () => {
    try {
        stopCamera(); // Ensure clean start
        stream.value = await navigator.mediaDevices.getUserMedia({ video: true });
        if (videoElement.value) {
            videoElement.value.srcObject = stream.value;
        }
        isCameraActive.value = true;
        cameraError.value = null;
    } catch (err) {
        console.error("Camera access error:", err);
        cameraError.value = "Akses kamera ditolak atau tidak tersedia.";
        isCameraActive.value = false;
    }
};

const toggleCamera = () => {
    if (isCameraActive.value) {
        stopCamera();
    } else {
        startCamera();
    }
};

const capturePhoto = () => {
    if (!videoElement.value || !isCameraActive.value) return;
    
    // Create canvas
    const canvas = document.createElement('canvas');
    canvas.width = videoElement.value.videoWidth;
    canvas.height = videoElement.value.videoHeight;
    
    const context = canvas.getContext('2d');
    context.drawImage(videoElement.value, 0, 0, canvas.width, canvas.height);
    
    // Store as Base64
    capturedImage.value = canvas.toDataURL('image/png');
};

const retakePhoto = () => {
    capturedImage.value = null;
    if(!isCameraActive.value) startCamera();
};

// -- Visibility API --
const handleVisibilityChange = () => {
    if (document.visibilityState === 'hidden') {
        stopCamera();
    }
};

// -- Lifecycle --
onMounted(() => {
    initMap();
    watchLocation();
    startCamera();
    updateTime();
    timerInterval = setInterval(updateTime, 1000);
    document.addEventListener('visibilitychange', handleVisibilityChange);
});

onUnmounted(() => {
    stopCamera();
    if (map.value) {
        map.value.remove();
    }
    if (timerInterval) clearInterval(timerInterval);
    document.removeEventListener('visibilitychange', handleVisibilityChange);
});

// -- Computed --
const formattedDistance = computed(() => {
    if (distanceFromOffice.value === null) return '-';
    if (distanceFromOffice.value >= 1000) {
        return (distanceFromOffice.value / 1000).toFixed(1) + ' km';
    }
    return distanceFromOffice.value + ' meter';
});

const isButtonEnabled = computed(() => {
    // If WFO -> Must be in range AND have photo
    // If WFH -> Range check bypassed, must have photo
    if (attendanceMode.value === 'WFO') {
        return isInRange.value && capturedImage.value !== null;
    } else {
        return capturedImage.value !== null;
    }
});

const buttonText = computed(() => {
    if (locationError.value) return 'Lokasi Error';
    if (isLoadingLocation.value) return 'Mendeteksi Lokasi...';
    
    if (attendanceMode.value === 'WFO' && !isInRange.value) {
        return `Diluar Jangkauan (${formattedDistance.value})`;
    }
    
    if (!capturedImage.value) return 'Ambil Foto Dahulu';
    
    return `Masuk Sekarang - ${currentTime.value}`;
});

const zoneStatusText = computed(() => {
    if (isLoadingLocation.value) return 'Mencari Lokasi...';
    if (isInRange.value) return props.officeName;
    return `Diluar Jangkauan (${formattedDistance.value})`;
});

const actionButtonClass = computed(() => {
    if (isButtonEnabled.value) {
        return 'bg-success hover:bg-emerald-600 shadow-success/20 animate-pulse-subtle';
    } 
    return 'bg-slate-300 cursor-not-allowed text-slate-500';
});
</script>

<template>
    <section class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div class="flex items-center gap-4">
                 <h2 class="text-xl font-bold flex items-center gap-2 text-text-main">
                    <MapPin class="text-primary w-6 h-6" />
                    Absensi Kehadiran
                </h2>
                
                <!-- Attendance Mode Toggle -->
                <div class="flex bg-slate-100 p-1 rounded-lg">
                    <button 
                        @click="attendanceMode = 'WFO'"
                        class="px-3 py-1 rounded-md text-xs font-bold transition-all flex items-center gap-1.5"
                        :class="attendanceMode === 'WFO' ? 'bg-white text-primary shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                    >
                        <Building2 class="w-3.5 h-3.5" /> WFO
                    </button>
                    <button 
                        @click="attendanceMode = 'WFH'"
                        class="px-3 py-1 rounded-md text-xs font-bold transition-all flex items-center gap-1.5"
                        :class="attendanceMode === 'WFH' ? 'bg-white text-primary shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                    >
                        <Home class="w-3.5 h-3.5" /> WFH
                    </button>
                </div>
            </div>

            <div class="flex items-center gap-2">
                 <span v-if="distanceFromOffice !== null" class="text-xs font-mono font-bold" :class="isInRange ? 'text-success' : 'text-danger'">
                    {{ formattedDistance }}
                </span>
                <span class="text-xs font-mono bg-slate-100 px-3 py-1 rounded-full text-text-secondary border border-slate-200 flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    GPS Live
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Map Section -->
            <div class="relative group overflow-hidden rounded-xl h-64 lg:h-80 w-full bg-slate-100 border border-slate-200 shadow-inner">
                <div ref="mapContainer" class="absolute inset-0 z-0"></div>
                
                <!-- Zone Overlay -->
                <div class="absolute inset-x-4 bottom-4 z-10">
                    <div class="bg-white/95 backdrop-blur-sm p-3 rounded-lg flex items-center gap-3 shadow-lg border border-slate-100">
                        <div class="p-2 rounded-md" :class="isInRange ? 'bg-blue-50 text-success' : 'bg-red-50 text-danger'">
                            <MapPin class="w-5 h-5" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[10px] font-bold uppercase text-text-secondary tracking-wider">Zona Saat Ini</p>
                            <p class="text-sm font-bold truncate" :class="isInRange ? 'text-slate-900' : 'text-danger'">
                                {{ zoneStatusText }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Locate Loading Overlay -->
                <div v-if="isLoadingLocation" class="absolute inset-0 bg-white/50 backdrop-blur-sm flex items-center justify-center z-20">
                     <div class="flex flex-col items-center gap-2">
                        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-primary"></div>
                        <span class="text-xs font-bold text-primary">Mencari Lokasi...</span>
                     </div>
                </div>
            </div>

            <!-- Camera Section -->
            <div class="flex flex-col gap-4 h-full">
                <div class="relative flex-1 bg-slate-900 rounded-xl overflow-hidden min-h-[220px] flex items-center justify-center group border border-slate-800 shadow-inner">
                    <!-- Camera Stream -->
                    <video ref="videoElement" autoplay playsinline muted class="absolute inset-0 w-full h-full object-cover transform scale-x-[-1]" :class="{ 'opacity-0': capturedImage || !isCameraActive }"></video>
                    
                    <!-- Captured Image Preview -->
                    <img v-if="capturedImage" :src="capturedImage" class="absolute inset-0 w-full h-full object-cover transform scale-x-[-1]" />

                    <!-- Scan Line Animation -->
                    <div v-if="!capturedImage && isCameraActive" class="absolute inset-0 bg-gradient-to-b from-transparent via-emerald-500/10 to-transparent z-10 animate-scan"></div>
                    
                    <!-- Camera Off State -->
                    <div v-if="!isCameraActive && !capturedImage && !cameraError" class="absolute inset-0 bg-slate-900 flex flex-col items-center justify-center text-slate-500 z-10">
                        <VideoOff class="w-12 h-12 mb-2 opacity-50" />
                        <span class="text-sm font-medium">Kamera Non-aktif</span>
                    </div>

                    <!-- Empty/Error State -->
                    <div v-if="cameraError" class="absolute inset-0 bg-slate-800 flex flex-col items-center justify-center text-slate-400 p-4 text-center z-20">
                        <AlertCircle class="w-10 h-10 mb-2 text-danger" />
                        <span class="text-sm">{{ cameraError }}</span>
                    </div>

                    <!-- Controls Overlay -->
                    <div class="absolute top-4 right-4 z-20 flex gap-2">
                         <!-- Camera Toggle -->
                        <button @click="toggleCamera" class="p-2 rounded-full bg-black/40 text-white backdrop-blur-md hover:bg-black/60 transition-colors" title="Toggle Camera">
                            <component :is="isCameraActive ? Video : VideoOff" class="w-4 h-4" />
                        </button>
                        
                        <!-- Live Badge -->
                        <div v-if="isCameraActive && !capturedImage" class="bg-danger/90 backdrop-blur text-white text-[10px] font-bold px-2 py-1 rounded animate-pulse flex items-center gap-1 shadow-sm">
                            <span class="w-1.5 h-1.5 rounded-full bg-white"></span>
                            LIVE
                        </div>
                    </div>

                    <!-- Capture Controls -->
                    <div class="absolute bottom-4 inset-x-0 flex justify-center z-20">
                        <button v-if="!capturedImage" @click="capturePhoto" :disabled="!isCameraActive" class="w-14 h-14 rounded-full border-4 border-white bg-white/20 hover:bg-white/40 transition-all flex items-center justify-center backdrop-blur-sm active:scale-95 shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
                            <div class="w-10 h-10 rounded-full bg-white"></div>
                        </button>
                        <button v-else @click="retakePhoto" class="px-4 py-2 rounded-full bg-slate-900/80 text-white backdrop-blur-md border border-white/20 flex items-center gap-2 hover:bg-black transition-all">
                            <RefreshCw class="w-4 h-4" />
                            <span>Foto Ulang</span>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <button 
                    :disabled="!isButtonEnabled"
                    class="w-full py-4 text-white rounded-xl font-bold text-lg shadow-lg transition-all active:scale-95 flex items-center justify-center gap-3 group/btn relative overflow-hidden"
                    :class="actionButtonClass"
                >
                    <div class="absolute inset-0 bg-white/20 translate-y-full group-hover/btn:translate-y-0 transition-transform duration-300"></div>
                    <span class="bg-white/20 rounded-full p-1.5 relative z-10 transition-transform group-hover/btn:scale-110">
                        <Fingerprint class="w-6 h-6" />
                    </span>
                    <span class="relative z-10 font-display tracking-wide">{{ buttonText }}</span>
                </button>
            </div>
        </div>
    </section>
</template>

<style scoped>
@keyframes scan {
    0% { transform: translateY(-100%); }
    100% { transform: translateY(100%); }
}
.animate-scan {
    animation: scan 3s linear infinite;
}
</style>
