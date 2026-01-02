<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import axios from 'axios';
import {
    Search, Star, CheckCircle2, X, User, Award, FileText, Plus, Trash2, AlertCircle, Eye
} from 'lucide-vue-next';
import RichTextEditor from '@/Components/RichTextEditor.vue';

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps({
    mentees: Array,
    stats: Object,
    filters: Object,
});

const searchQuery = ref(props.filters?.search || '');
const activeStatus = ref(props.filters?.status || 'all');

const statusTabs = computed(() => [
    { key: 'all', label: 'Semua', count: props.stats?.total || 0 },
    { key: 'pending', label: 'Belum Selesai', count: props.stats?.pending || 0 },
    { key: 'completed', label: 'Selesai', count: props.stats?.completed || 0 },
]);

const showEvalModal = ref(false);
const showDetailModal = ref(false);
const selectedMentee = ref(null);
const isSubmitting = ref(false);
const actionMessage = ref(null);

const evalForm = ref({
    technical_skill: 0,
    communication: 0,
    teamwork: 0,
    initiative: 0,
    punctuality: 0,
    custom_criteria: [],
    strengths: '',
    improvements: '',
    recommendation: '',
    final_notes: '',
});

// Auto-calculate overall rating from all criteria
const calculatedOverallRating = computed(() => {
    const standardRatings = [
        evalForm.value.technical_skill,
        evalForm.value.communication,
        evalForm.value.teamwork,
        evalForm.value.initiative,
        evalForm.value.punctuality,
    ];
    const customRatings = evalForm.value.custom_criteria.map(c => c.rating || 0);
    const allRatings = [...standardRatings, ...customRatings];
    return allRatings.length > 0 ? Math.round(allRatings.reduce((a, b) => a + b, 0) / allRatings.length) : 0;
});

const ratingLabels = {
    technical_skill: 'Kemampuan Teknis',
    communication: 'Komunikasi',
    teamwork: 'Kerja Tim',
    initiative: 'Inisiatif',
    punctuality: 'Kedisiplinan',
};

const applyFilters = () => {
    router.get(route('mentor.evaluation'), {
        search: searchQuery.value,
        status: activeStatus.value,
    }, { preserveState: true, preserveScroll: true });
};

watch(activeStatus, () => applyFilters());

let searchTimeout;
const handleSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 500);
};

const openEvalModal = async (mentee) => {
    if (!mentee.can_evaluate) {
        actionMessage.value = { type: 'error', text: 'Evaluasi hanya bisa dilakukan saat progress magang sudah mencapai 90%.' };
        return;
    }
    selectedMentee.value = mentee;
    try {
        const response = await axios.get(route('mentor.evaluation.show', mentee.id));
        if (response.data.evaluation) {
            evalForm.value = {
                ...response.data.evaluation,
                custom_criteria: response.data.evaluation.custom_criteria || [],
            };
        } else {
            resetForm();
        }
        showEvalModal.value = true;
    } catch (error) {
        actionMessage.value = { type: 'error', text: 'Gagal memuat data evaluasi' };
    }
};

const openDetailModal = async (mentee) => {
    selectedMentee.value = mentee;
    try {
        const response = await axios.get(route('mentor.evaluation.show', mentee.id));
        selectedMentee.value = { ...mentee, ...response.data };
        showDetailModal.value = true;
    } catch (error) {
        actionMessage.value = { type: 'error', text: 'Gagal memuat detail' };
    }
};

const resetForm = () => {
    evalForm.value = {
        technical_skill: 0, communication: 0, teamwork: 0, initiative: 0,
        punctuality: 0, overall_rating: 0, custom_criteria: [], strengths: '',
        improvements: '', recommendation: '', final_notes: '',
    };
};

const closeModals = () => {
    showEvalModal.value = false;
    showDetailModal.value = false;
    selectedMentee.value = null;
};

const addCustomCriteria = () => {
    evalForm.value.custom_criteria.push({ name: '', rating: 0 });
};

const removeCustomCriteria = (index) => {
    evalForm.value.custom_criteria.splice(index, 1);
};

const saveEvaluation = async (submit = false) => {
    const ratings = ['technical_skill', 'communication', 'teamwork', 'initiative', 'punctuality'];
    for (const field of ratings) {
        if (evalForm.value[field] === null || evalForm.value[field] === '' || evalForm.value[field] < 0 || evalForm.value[field] > 100) {
            actionMessage.value = { type: 'error', text: `Mohon isi nilai 0-100 untuk ${ratingLabels[field]}` };
            return;
        }
    }

    for (const criteria of evalForm.value.custom_criteria) {
        if (!criteria.name.trim()) {
            actionMessage.value = { type: 'error', text: 'Nama kriteria tambahan tidak boleh kosong' };
            return;
        }
        if (criteria.rating === null || criteria.rating === '' || criteria.rating < 0 || criteria.rating > 100) {
            actionMessage.value = { type: 'error', text: `Mohon isi nilai 0-100 untuk kriteria "${criteria.name}"` };
            return;
        }
    }

    isSubmitting.value = true;
    try {
        const response = await axios.post(route('mentor.evaluation.store', selectedMentee.value.id), {
            ...evalForm.value,
            submit,
        });
        if (response.data.success) {
            actionMessage.value = { type: 'success', text: response.data.message };
            closeModals();
            router.reload({ only: ['mentees', 'stats'] });
        }
    } catch (error) {
        actionMessage.value = { type: 'error', text: error.response?.data?.message || 'Terjadi kesalahan' };
    } finally {
        isSubmitting.value = false;
    }
};

const getEvalStatusColor = (evaluation) => {
    if (!evaluation) return 'bg-slate-100 text-slate-600';
    return evaluation.status === 'submitted' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700';
};

const getEvalStatusLabel = (evaluation) => {
    if (!evaluation) return 'Belum Dibuat';
    return evaluation.status === 'submitted' ? 'Selesai' : 'Draft';
};
</script>

<template>

    <Head title="Evaluasi Magang" />

    <div class="flex flex-col gap-6">
        <!-- Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 tracking-tight">Evaluasi Magang</h1>
                <p class="text-slate-500 mt-1">Berikan penilaian akhir untuk mentee Anda (progress >= 90%).</p>
            </div>
        </div>

        <!-- Action Message -->
        <div v-if="actionMessage" class="p-4 rounded-xl flex items-center gap-2"
            :class="actionMessage.type === 'success' ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700'">
            <CheckCircle2 v-if="actionMessage.type === 'success'" class="w-5 h-5" />
            <AlertCircle v-else class="w-5 h-5" />
            {{ actionMessage.text }}
            <button @click="actionMessage = null" class="ml-auto cursor-pointer">
                <X class="w-4 h-4" />
            </button>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-3 gap-3">
            <div class="bg-white p-4 rounded-xl border border-slate-200 text-center">
                <p class="text-2xl font-black text-slate-900">{{ stats?.total || 0 }}</p>
                <p class="text-xs text-slate-500">Total Mentee</p>
            </div>
            <div class="bg-amber-50 p-4 rounded-xl border border-amber-200 text-center">
                <p class="text-2xl font-black text-amber-600">{{ stats?.pending || 0 }}</p>
                <p class="text-xs text-amber-600">Belum Selesai</p>
            </div>
            <div class="bg-emerald-50 p-4 rounded-xl border border-emerald-200 text-center">
                <p class="text-2xl font-black text-emerald-600">{{ stats?.completed || 0 }}</p>
                <p class="text-xs text-emerald-600">Selesai</p>
            </div>
        </div>

        <!-- List Card -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-200 flex flex-col lg:flex-row justify-between gap-4 bg-slate-50/50">
                <div class="flex p-1 bg-slate-100 rounded-xl overflow-x-auto">
                    <button v-for="tab in statusTabs" :key="tab.key" @click="activeStatus = tab.key"
                        class="px-4 py-2 text-sm rounded-lg font-medium whitespace-nowrap transition-all cursor-pointer"
                        :class="activeStatus === tab.key ? 'bg-white text-primary shadow-sm' : 'text-slate-500 hover:text-slate-700'">
                        {{ tab.label }} <span class="ml-1 text-xs">({{ tab.count }})</span>
                    </button>
                </div>

                <div class="relative">
                    <Search class="absolute left-3 top-[40%] -translate-y-1/2 w-4 h-4 text-slate-400" />
                    <input v-model="searchQuery" @input="handleSearch" type="text" placeholder="Cari mentee..."
                        class="pl-9 pr-4 py-2 w-64 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-primary/20" />
                </div>
            </div>

            <!-- Mentee Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead
                        class="bg-slate-50 text-slate-500 font-semibold uppercase text-xs tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4">Mentee</th>
                            <th class="px-6 py-4">Posisi</th>
                            <th class="px-6 py-4">Periode</th>
                            <th class="px-6 py-4">Progress</th>
                            <th class="px-6 py-4">Rating</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4 w-32">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="mentee in mentees" :key="mentee.id"
                            class="group hover:bg-blue-50/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary text-sm font-bold shrink-0">
                                        {{ mentee.intern_name?.charAt(0) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-900">{{ mentee.intern_name }}</p>
                                        <p class="text-xs text-slate-400">{{ mentee.intern_email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-slate-600">{{ mentee.position }}</td>
                            <td class="px-6 py-4 text-slate-500 text-xs whitespace-nowrap">
                                {{ mentee.start_date }} - {{ mentee.end_date }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-20 bg-slate-100 rounded-full h-2">
                                        <div class="h-full rounded-full transition-all"
                                            :class="mentee.can_evaluate ? 'bg-emerald-500' : 'bg-primary'"
                                            :style="{ width: `${mentee.progress}%` }"></div>
                                    </div>
                                    <span class="text-xs font-semibold"
                                        :class="mentee.can_evaluate ? 'text-emerald-600' : 'text-slate-600'">
                                        {{ Math.round(mentee.progress) }}%
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div v-if="mentee.evaluation?.overall_rating" class="flex flex-col items-center gap-1">
                                    <span class="text-lg font-bold text-primary">{{ mentee.evaluation.overall_rating
                                        }}</span>
                                    <span class="text-xs px-2 py-0.5 rounded-full" :class="{
                                        'bg-emerald-100 text-emerald-700': mentee.evaluation.overall_rating >= 90,
                                        'bg-blue-100 text-blue-700': mentee.evaluation.overall_rating >= 80 && mentee.evaluation.overall_rating < 90,
                                        'bg-amber-100 text-amber-700': mentee.evaluation.overall_rating >= 70 && mentee.evaluation.overall_rating < 80,
                                        'bg-red-100 text-red-700': mentee.evaluation.overall_rating < 70
                                    }">
                                        {{ mentee.evaluation.overall_rating >= 90 ? 'Sangat Baik' :
                                            mentee.evaluation.overall_rating >= 80 ? 'Baik' :
                                                mentee.evaluation.overall_rating >= 70 ? 'Cukup' : 'Kurang' }}
                                    </span>
                                </div>
                                <span v-else class="text-slate-400 text-xs">-</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold whitespace-nowrap"
                                    :class="getEvalStatusColor(mentee.evaluation)">
                                    {{ getEvalStatusLabel(mentee.evaluation) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <button v-if="!mentee.evaluation || mentee.evaluation.status === 'draft'"
                                    @click="openEvalModal(mentee)" :disabled="!mentee.can_evaluate"
                                    class="px-3 py-1.5 rounded-lg text-xs font-semibold flex items-center gap-1" :class="mentee.can_evaluate
                                        ? 'bg-primary text-white hover:bg-primary-hover cursor-pointer'
                                        : 'bg-slate-100 text-slate-400 cursor-not-allowed'">
                                    <Award class="w-3.5 h-3.5" />
                                    {{ mentee.evaluation ? 'Edit' : 'Evaluasi' }}
                                </button>
                                <div v-else class="flex items-center gap-2">
                                    <a :href="route('mentor.evaluation.certificate', mentee.id)" target="_blank"
                                        class="px-3 py-1.5 bg-primary text-white rounded-lg text-xs font-semibold hover:bg-primary-hover cursor-pointer flex items-center gap-1">
                                        <Eye class="w-3.5 h-3.5" />
                                        Preview
                                    </a>
                                    <button @click="openDetailModal(mentee)"
                                        class="px-3 py-1.5 bg-slate-100 text-slate-700 rounded-lg text-xs font-semibold hover:bg-slate-200 cursor-pointer flex items-center gap-1">
                                        <FileText class="w-3.5 h-3.5" />
                                        Detail
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!mentees?.length">
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                                <User class="w-12 h-12 mx-auto mb-3 opacity-50" />
                                <p>Tidak ada mentee untuk ditampilkan.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Evaluation Form Modal -->
    <Teleport to="body">
        <div v-if="showEvalModal && selectedMentee" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModals"></div>
            <div
                class="relative bg-white w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">
                <div class="p-5 border-b border-slate-200 flex justify-between items-start shrink-0">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Formulir Evaluasi</h3>
                        <p class="text-sm text-slate-500">{{ selectedMentee.intern_name }} - {{ selectedMentee.position
                        }}</p>
                    </div>
                    <button @click="closeModals" class="p-2 hover:bg-slate-100 rounded-lg cursor-pointer">
                        <X class="w-5 h-5 text-slate-400" />
                    </button>
                </div>

                <div class="p-5 flex flex-col gap-6 overflow-y-auto">
                    <!-- Standard Rating Section -->
                    <div class="space-y-4">
                        <h4 class="font-semibold text-slate-900 flex items-center gap-2">
                            <Award class="w-4 h-4 text-primary" />
                            Kriteria Standar (0-100)
                        </h4>

                        <div v-for="(label, field) in ratingLabels" :key="field"
                            class="flex items-center justify-between gap-4 p-3 bg-slate-50 rounded-lg">
                            <span class="text-sm text-slate-600 flex-1">{{ label }}</span>
                            <div class="flex items-center gap-2">
                                <input v-model.number="evalForm[field]" type="number" min="0" max="100"
                                    class="w-20 px-3 py-2 border border-slate-200 rounded-lg text-sm text-center font-semibold focus:ring-2 focus:ring-primary/20" />
                                <span class="text-xs text-slate-400">/100</span>
                            </div>
                        </div>
                    </div>

                    <!-- Custom Criteria Section -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h4 class="font-semibold text-slate-900">Kriteria Tambahan (Opsional)</h4>
                            <button @click="addCustomCriteria"
                                class="flex items-center gap-1 px-3 py-1.5 text-xs font-semibold text-primary hover:bg-primary/10 rounded-lg cursor-pointer">
                                <Plus class="w-4 h-4" /> Tambah Kriteria
                            </button>
                        </div>

                        <div v-for="(criteria, index) in evalForm.custom_criteria" :key="index"
                            class="flex items-center gap-3 p-3 bg-slate-50 rounded-xl">
                            <input v-model="criteria.name" type="text" placeholder="Nama kriteria..."
                                class="flex-1 px-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-primary/20" />
                            <div class="flex items-center gap-2">
                                <input v-model.number="criteria.rating" type="number" min="0" max="100"
                                    class="w-20 px-3 py-2 border border-slate-200 rounded-lg text-sm text-center font-semibold focus:ring-2 focus:ring-primary/20" />
                                <span class="text-xs text-slate-400">/100</span>
                            </div>
                            <button @click="removeCustomCriteria(index)"
                                class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg cursor-pointer">
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>

                        <p v-if="evalForm.custom_criteria.length === 0" class="text-xs text-slate-400 text-center py-2">
                            Belum ada kriteria tambahan. Klik "Tambah Kriteria" untuk menambahkan.
                        </p>
                    </div>

                    <!-- Calculated Overall Rating Display -->
                    <div class="p-4 bg-gradient-to-r from-primary/10 to-blue-50 rounded-xl border border-primary/20">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <Award class="w-5 h-5 text-primary" />
                                <span class="font-semibold text-slate-700">Nilai Keseluruhan (Rata-rata)</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-3xl font-bold text-primary">{{ calculatedOverallRating }}</span>
                                <span class="text-sm text-slate-500">/100</span>
                            </div>
                        </div>
                        <p class="text-xs text-slate-500 mt-2">
                            Dihitung otomatis dari rata-rata semua kriteria (standar + tambahan)
                        </p>
                    </div>

                    <!-- Text Feedback Section -->
                    <div class="space-y-4">
                        <h4 class="font-semibold text-slate-900">Umpan Balik</h4>

                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">Kelebihan</label>
                            <RichTextEditor v-model="evalForm.strengths" placeholder="Jelaskan kelebihan mentee..."
                                minHeight="80px" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">Area Pengembangan</label>
                            <RichTextEditor v-model="evalForm.improvements"
                                placeholder="Jelaskan area yang perlu ditingkatkan..." minHeight="80px" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">Rekomendasi</label>
                            <RichTextEditor v-model="evalForm.recommendation"
                                placeholder="Berikan rekomendasi untuk mentee..." minHeight="80px" />
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">Catatan Akhir</label>
                            <RichTextEditor v-model="evalForm.final_notes" placeholder="Catatan tambahan..."
                                minHeight="80px" />
                        </div>
                    </div>
                </div>

                <div class="p-5 border-t border-slate-200 flex gap-3 shrink-0">
                    <button @click="saveEvaluation(false)" :disabled="isSubmitting"
                        class="flex-1 px-4 py-2.5 border border-slate-200 text-slate-700 rounded-xl font-semibold hover:bg-slate-50 disabled:opacity-50 cursor-pointer">
                        {{ isSubmitting ? 'Menyimpan...' : 'Simpan Draft' }}
                    </button>
                    <button @click="saveEvaluation(true)" :disabled="isSubmitting"
                        class="flex-1 px-4 py-2.5 bg-primary text-white rounded-xl font-semibold hover:bg-primary-hover disabled:opacity-50 cursor-pointer">
                        {{ isSubmitting ? 'Mengirim...' : 'Kirim & Buat Sertifikat' }}
                    </button>
                </div>
            </div>
        </div>
    </Teleport>

    <!-- Detail Modal -->
    <Teleport to="body">
        <div v-if="showDetailModal && selectedMentee?.evaluation"
            class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModals"></div>
            <div
                class="relative bg-white w-full max-w-2xl rounded-2xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">
                <div class="p-5 border-b border-slate-200 flex justify-between items-start shrink-0">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Detail Evaluasi</h3>
                        <p class="text-sm text-slate-500">{{ selectedMentee.intern_name }}</p>
                    </div>
                    <button @click="closeModals" class="p-2 hover:bg-slate-100 rounded-lg cursor-pointer">
                        <X class="w-5 h-5 text-slate-400" />
                    </button>
                </div>

                <div class="p-5 flex flex-col gap-6 overflow-y-auto">
                    <!-- Standard Ratings -->
                    <div class="grid grid-cols-2 gap-4">
                        <div v-for="(label, field) in ratingLabels" :key="field"
                            class="p-3 bg-slate-50 rounded-xl flex items-center justify-between">
                            <p class="text-xs text-slate-500">{{ label }}</p>
                            <span class="text-lg font-bold text-primary">{{ selectedMentee.evaluation[field] }}</span>
                        </div>
                    </div>

                    <!-- Custom Criteria -->
                    <div v-if="selectedMentee.evaluation.custom_criteria?.length" class="space-y-2">
                        <h4 class="text-sm font-semibold text-slate-700">Kriteria Tambahan</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div v-for="(criteria, idx) in selectedMentee.evaluation.custom_criteria" :key="idx"
                                class="p-3 bg-blue-50 rounded-xl flex items-center justify-between">
                                <p class="text-xs text-blue-600">{{ criteria.name }}</p>
                                <span class="text-lg font-bold text-blue-700">{{ criteria.rating }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Feedback -->
                    <div v-if="selectedMentee.evaluation.strengths" class="p-4 bg-emerald-50 rounded-xl">
                        <p class="text-xs font-semibold text-emerald-600 mb-1">Kelebihan</p>
                        <div class="rich-content text-sm text-emerald-800" v-html="selectedMentee.evaluation.strengths">
                        </div>
                    </div>

                    <div v-if="selectedMentee.evaluation.improvements" class="p-4 bg-amber-50 rounded-xl">
                        <p class="text-xs font-semibold text-amber-600 mb-1">Area Pengembangan</p>
                        <div class="rich-content text-sm text-amber-800"
                            v-html="selectedMentee.evaluation.improvements"></div>
                    </div>

                    <div v-if="selectedMentee.evaluation.recommendation" class="p-4 bg-blue-50 rounded-xl">
                        <p class="text-xs font-semibold text-blue-600 mb-1">Rekomendasi</p>
                        <div class="rich-content text-sm text-blue-800"
                            v-html="selectedMentee.evaluation.recommendation"></div>
                    </div>

                    <div v-if="selectedMentee.evaluation.final_notes" class="p-4 bg-slate-100 rounded-xl">
                        <p class="text-xs font-semibold text-slate-600 mb-1">Catatan Akhir</p>
                        <div class="rich-content text-sm text-slate-800" v-html="selectedMentee.evaluation.final_notes">
                        </div>
                    </div>

                    <div class="text-xs text-slate-400 text-center">
                        Dikirim pada {{ selectedMentee.evaluation.submitted_at }}
                    </div>
                </div>

                <div class="p-5 border-t border-slate-200 shrink-0">
                    <button @click="closeModals"
                        class="w-full px-4 py-2.5 bg-slate-100 text-slate-700 rounded-xl font-semibold hover:bg-slate-200 cursor-pointer">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>
