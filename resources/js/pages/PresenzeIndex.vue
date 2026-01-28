<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '../layouts/AppLayout.vue';

const props = defineProps({
  bambini: Array,
  presenzeEsistenti: Object, // Keyed by bambino_id
  dataSelezionata: String,
  classi: Array,
  filters: Object
});

// Gestione cambio data
const onDateChange = (e) => {
  router.get('/presenze', { ...props.filters, data_selezionata: e.target.value }, {
    preserveState: true,
    preserveScroll: true,
    only: ['presenzeEsistenti', 'dataSelezionata', 'bambini']
  });
};

// Gestione Filtro Classe
const onClasseChange = (e) => {
  router.get('/presenze', { ...props.filters, classe_id: e.target.value }, {
    preserveState: true,
    preserveScroll: true,
  });
};

// --- LOGICA SALVATAGGIO SINGOLO ---
// Usiamo un form generico che viene popolato al momento del click
const form = useForm({
  bambino_id: '',
  data_selezionata: props.dataSelezionata,
  orario_ingresso: '',
  orario_uscita: '',
  assenza_motivo: '',
  accompagnatore_ingresso: '',
  accompagnatore_uscita: ''
});
console.log(form.bambino_id);
// Funzione helper per ottenere la presenza corrente (safe)
const getPresenza = (bambinoId) => {
  return (props.presenzeEsistenti && props.presenzeEsistenti[bambinoId]) || null;
};

// Azione: Segna Ingresso
const segnaIngresso = (bambinoId) => {
  const now = new Date().toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit' });
  const presenza = getPresenza(bambinoId);

  form.bambino_id = bambinoId;
  form.data_selezionata = props.dataSelezionata;
  form.orario_ingresso = presenza?.ora_in_fmt || now; // Se esiste usa quello, se no usa ADESSO
  form.assenza_motivo = null; // Resetta assenza
  form.orario_uscita = presenza?.ora_out_fmt || null;
  
  submitForm();
};

// Azione: Segna Uscita
const segnaUscita = (bambinoId) => {
  const now = new Date().toLocaleTimeString('it-IT', { hour: '2-digit', minute: '2-digit' });
  const presenza = getPresenza(bambinoId);

  form.bambino_id = bambinoId;
  form.data_selezionata = props.dataSelezionata;
  form.orario_ingresso = presenza?.ora_in_fmt; // Mantieni ingresso
  form.orario_uscita = now;
  form.assenza_motivo = null;

  submitForm();
};

// Azione: Segna Assenza
const segnaAssenza = (bambinoId, motivo = 'Malattia') => {
  form.bambino_id = bambinoId;
  form.data_selezionata = props.dataSelezionata;
  form.assenza_motivo = motivo;
  form.orario_ingresso = null;
  form.orario_uscita = null;

  submitForm();
};

const submitForm = () => {
  form.post('/presenze', {
    preserveScroll: true,
    onSuccess: () => { /* Feedback automatico */ }
  });
};
</script>

<template>
  <AppLayout>
    <div class="max-w-6xl mx-auto pb-20">

      <!-- HEADER CONTROLLI -->
      <div class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 mb-8">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
          
          <div>
            <h2 class="font-serif text-3xl text-slate-800">Registro Presenze</h2>
            <p class="text-slate-500">Gestisci ingressi e uscite giornaliere</p>
          </div>

          <div class="flex gap-4">
            <!-- Selettore Classe -->
            <select :value="filters.classe_id" @change="onClasseChange" class="rounded-xl border-slate-200 text-slate-600 focus:ring-sky-200">
              <option value="">Tutte le classi</option>
              <option v-for="c in classi" :key="c.id" :value="c.id">{{ c.nome }}</option>
            </select>

            <!-- Selettore Data -->
            <input 
              type="date" 
              :value="dataSelezionata" 
              @change="onDateChange"
              class="rounded-xl border-slate-200 text-slate-600 font-bold focus:ring-sky-200" 
            />
          </div>
        </div>
      </div>

      <!-- GRID BAMBINI -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        <div 
          v-for="bambino in bambini" 
          :key="bambino.id"
          class="relative bg-white rounded-3xl p-5 border shadow-sm transition-all"
          :class="{
             'border-slate-100': !getPresenza(bambino.id),
             'border-emerald-200 bg-emerald-50/30': getPresenza(bambino.id)?.orario_ingresso && !getPresenza(bambino.id)?.assenza_motivo,
             'border-rose-200 bg-rose-50/30': getPresenza(bambino.id)?.assenza_motivo
          }"
        >
          <!-- Intestazione Card -->
          <div class="flex items-center gap-4 mb-4">
             <div class="w-14 h-14 rounded-full flex items-center justify-center text-2xl border-2 border-white shadow-sm"
                  :class="getPresenza(bambino.id)?.assenza_motivo ? 'bg-rose-100' : 'bg-sky-50'">
                {{ bambino.sesso === 'M' ? '👦' : '👧' }}
             </div>
             <div>
                <h3 class="font-bold text-slate-700 text-lg">{{ bambino.nome }} {{ bambino.cognome }}</h3>
                <!-- Badge Stato -->
                <span v-if="getPresenza(bambino.id)?.assenza_motivo" class="text-xs font-bold text-rose-600 bg-rose-100 px-2 py-1 rounded-md">
                   ASSENTE: {{ getPresenza(bambino.id).assenza_motivo }}
                </span>
                <span v-else-if="getPresenza(bambino.id)?.orario_ingresso" class="text-xs font-bold text-emerald-600 bg-emerald-100 px-2 py-1 rounded-md">
                   PRESENTE
                   <span v-if="getPresenza(bambino.id)?.orario_uscita"> (Uscito)</span>
                </span>
                <span v-else class="text-xs font-bold text-slate-400 bg-slate-100 px-2 py-1 rounded-md">DA REGISTRARE</span>
             </div>
          </div>

          <!-- Controlli -->
          <div class="space-y-3">
             
             <!-- SE È ASSENTE -->
             <div v-if="getPresenza(bambino.id)?.assenza_motivo">
                <button @click="segnaIngresso(bambino.id)" class="w-full py-2 text-sm text-slate-500 hover:text-sky-600 hover:bg-sky-50 rounded-xl border border-dashed border-slate-300">
                   Annulla Assenza (Segna Presente)
                </button>
             </div>

             <!-- ALTRIMENTI (Presente o Da registrare) -->
             <div v-else class="space-y-3">
                
                <!-- Ingresso -->
                <div class="flex items-center gap-2">
                   <div class="w-8 flex justify-center text-emerald-500">⬇️</div>
                   <div class="flex-1">
                      <label class="text-xs font-bold text-slate-400 uppercase">Ingresso</label>
                      <div class="flex gap-2">
                         <!-- Input orario statico se già presente, o editabile -->
                         <input 
                           type="time" 
                           :value="getPresenza(bambino.id)?.ora_in_fmt" 
                           @change="(e) => { form.orario_ingresso = e.target.value; segnaIngresso(bambino.id) }"
                           class="w-full rounded-xl border-slate-200 text-sm py-1.5 focus:ring-emerald-200"
                         />
                         <button 
                           v-if="!getPresenza(bambino.id)?.orario_ingresso" 
                           @click="segnaIngresso(bambino.id)"
                           class="bg-emerald-500 text-white px-3 rounded-xl text-sm font-bold shadow-sm hover:bg-emerald-600"
                         >OK</button>
                      </div>
                   </div>
                </div>

                <!-- Uscita (Abilitata solo se c'è ingresso) -->
                <div class="flex items-center gap-2" :class="{'opacity-50 grayscale': !getPresenza(bambino.id)?.orario_ingresso}">
                   <div class="w-8 flex justify-center text-orange-500">⬆️</div>
                   <div class="flex-1">
                      <label class="text-xs font-bold text-slate-400 uppercase">Uscita</label>
                      <div class="flex gap-2">
                         <input 
                           type="time" 
                           :disabled="!getPresenza(bambino.id)?.orario_ingresso"
                           :value="getPresenza(bambino.id)?.ora_out_fmt" 
                           @change="(e) => { form.orario_uscita = e.target.value; segnaUscita(bambino.id) }"
                           class="w-full rounded-xl border-slate-200 text-sm py-1.5 focus:ring-orange-200"
                         />
                         <button 
                           v-if="getPresenza(bambino.id)?.orario_ingresso && !getPresenza(bambino.id)?.orario_uscita" 
                           @click="segnaUscita(bambino.id)"
                           class="bg-orange-400 text-white px-3 rounded-xl text-sm font-bold shadow-sm hover:bg-orange-500"
                         >OK</button>
                      </div>
                   </div>
                </div>

                <!-- Tasto Segna Assenza (Solo se non ha ancora fatto check-in) -->
                <div v-if="!getPresenza(bambino.id)?.orario_ingresso" class="pt-2 border-t border-slate-100 mt-2">
                   <div class="flex gap-2">
                      <button @click="segnaAssenza(bambino.id, 'Malattia')" class="flex-1 py-1.5 text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-lg border border-rose-100">
                         🤒 Malattia
                      </button>
                      <button @click="segnaAssenza(bambino.id, 'Famiglia')" class="flex-1 py-1.5 text-xs font-bold text-slate-600 bg-slate-50 hover:bg-slate-100 rounded-lg border border-slate-200">
                         🏠 Famiglia
                      </button>
                   </div>
                </div>

             </div>
          </div>
          
          <!-- Loading overlay specifico (usando form.processing) -->
          <div v-if="form.processing && form.bambino_id === bambino.id" class="absolute inset-0 bg-white/60 backdrop-blur-sm rounded-3xl flex items-center justify-center z-10">
             <span class="animate-spin text-2xl">⏳</span>
          </div>

        </div>

      </div>
    </div>
  </AppLayout>
</template>