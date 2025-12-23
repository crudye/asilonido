<script setup>
import { ref, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '../layouts/AppLayout.vue';

// 1. Props ricevuti dal Controller
const props = defineProps({
  bambini: Array,
  diariEsistenti: Object, // Può essere null, array vuoto o oggetto
  dataSelezionata: String,
  classi: Array
});

// 2. Stato locale per la selezione UI
// Usiamo .id (stringa) invece di ._id
const initialId = props.bambini && props.bambini.length > 0 ? props.bambini[0].id : null;
const selectedBambinoId = ref(initialId);
let dataDiOggi = '';

// 3. Setup del Form Inertia
const form = useForm({
  bambino_id: selectedBambinoId.value,
  data: props.dataSelezionata,
  pasti: { primo: 'tutto', secondo: 'metà', merenda: 'niente' },
  sonno: { da: '', a: '' },
  bisogni: [], 
  attivita: '',
  umore: 'sereno'
});

// 4. Watcher: Gestisce il cambio bambino (Popola o Resetta)
watch(selectedBambinoId, (newId) => {
  
  if (!newId) return;

  form.clearErrors();
  form.bambino_id = newId;
  
  // Gestiamo il caso in cui diariEsistenti sia null o undefined
  const raccoltaDiari = props.diariEsistenti || {};
  //console.log('Diari esistenti:', raccoltaDiari);
  const diarioEsistente = raccoltaDiari[newId];

  if (diarioEsistente) {
    // --- MODALITÀ MODIFICA ---
    // Usiamo '??' (nullish coalescing) per mantenere valori validi (anche se vuoti)
    form.pasti = diarioEsistente.pasti ?? { primo: 'tutto', secondo: 'metà', merenda: 'niente' };
    form.sonno = diarioEsistente.sonno ?? { da: '', a: '' };
    form.bisogni = diarioEsistente.bisogni ?? [];
    form.attivita = diarioEsistente.attivita ?? '';
    form.umore = diarioEsistente.umore ?? 'sereno';
  } else {
    // --- MODALITÀ CREAZIONE ---
    // Resettiamo i campi mantenendo ID e Data
    form.reset('pasti', 'sonno', 'bisogni', 'attivita', 'umore');
    // Forziamo i default per UX migliore
    form.pasti = { primo: 'tutto', secondo: 'tutto', merenda: 'tutto' }; 
    form.bambino_id = newId; // Re-imposta ID perché il reset lo pulisce
  }
});

// 5. Gestione cambio data
const onDateChange = (e) => {
  dataDiOggi = e.target.value;
  router.get('/diario', { data: e.target.value }, {
    preserveState: true,
    preserveScroll: true,
    only: ['diariEsistenti', 'dataSelezionata']
  });
};

const addBisogno = () => {
  form.bisogni.push({ ora: '10:00', tipo: 'pipì' });
};

const salvaDiario = () => {
  form
  .transform((data) => ({
        ...data,
        giorno: dataDiOggi, 
    }))
  .post('/diario', {
    preserveScroll: true,
    onSuccess: () => {
        // Feedback gestito automaticamente da Inertia/Flash message
    }
  });
};
</script>

<template>
  <AppLayout>
    <div class="max-w-4xl mx-auto pb-20">
      
      <!-- HEADER E DATA -->
      <div class="flex flex-col md:flex-row justify-between items-center mb-6 mt-6 border-b border-slate-200 pb-4 gap-4">
        <div>
            <h2 class="font-serif text-3xl text-slate-800">Diario Giornaliero</h2>
            <p class="text-slate-500 text-sm">Seleziona un bambino e compila la scheda</p>
        </div>
        <div class="bg-white p-2 rounded-xl border border-slate-200 shadow-sm">
          <input 
            type="date" 
            :value="dataSelezionata" 
            @change="onDateChange"
            class="bg-transparent font-bold text-slate-600 border-none focus:ring-0 cursor-pointer outline-none" 
          />
        </div>
      </div>

      <!-- SELETTORE BAMBINI -->
      <div class="mb-8 mt-6 overflow-x-auto pb-4 scrollbar-hide">
        <div class="flex gap-4 min-w-max px-1 mt-2">
           <button 
             v-for="bambino in bambini" 
             :key="bambino.id"
             @click="selectedBambinoId = bambino.id"
             type="button"
             class="relative flex flex-col items-center gap-2 p-3 rounded-2xl transition-all border-2 w-24"
             :class="selectedBambinoId === bambino.id ? 'bg-sky-50 border-sky-400 scale-105 shadow-md' : 'bg-white border-transparent hover:bg-slate-50'"
           >
             <!-- Indicatore Completato (Pallino Verde) -->
             <!-- Controllo sicuro su diariEsistenti -->
             <div 
                v-if="diariEsistenti && diariEsistenti[bambino.id]" 
                class="absolute top-2 right-2 w-3 h-3 bg-emerald-400 rounded-full border-2 border-white shadow-sm"
             ></div>

             <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-xl shadow-inner border border-slate-200">
               {{ bambino.sesso === 'M' ? '👦' : '👧' }}
             </div>
             <span class="text-xs font-bold text-slate-700 truncate w-full text-center">{{ bambino.nome }}</span>
           </button>
        </div>
      </div>

      <!-- FORM -->
      <form @submit.prevent="salvaDiario" class="space-y-6 relative">
        
        <!-- Loading Overlay -->
        <div v-if="form.processing" class="absolute inset-0 bg-white/50 z-10 flex items-center justify-center rounded-3xl backdrop-blur-sm transition-all">
            <span class="font-serif text-xl text-sky-600 animate-pulse font-bold">Salvataggio in corso... 🧸</span>
        </div>

        <!-- Sezione Pasti (Verde) -->
        <section class="bg-emerald-50 rounded-3xl p-6 border border-emerald-100 shadow-sm">
          <h3 class="font-serif text-xl text-emerald-800 mb-4 flex items-center gap-2">
            🍎 La Pappa
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div v-for="piatto in ['primo', 'secondo', 'merenda']" :key="piatto">
              <label class="block text-sm font-bold text-emerald-700 uppercase mb-2">{{ piatto }}</label>
              <select v-model="form.pasti[piatto]" class="w-full rounded-xl border-emerald-200 focus:ring-emerald-300 bg-white py-2 px-3 text-emerald-900 shadow-sm">
                <option value="tutto">Ha mangiato tutto 😋</option>
                <option value="metà">Ne ha mangiato metà 😐</option>
                <option value="niente">Non ha mangiato 😔</option>
              </select>
            </div>
          </div>
        </section>

        <!-- Sezione Nanna (Blu) -->
        <section class="bg-indigo-50 rounded-3xl p-6 border border-indigo-100 shadow-sm">
          <h3 class="font-serif text-xl text-indigo-800 mb-4 flex items-center gap-2">
            💤 Il Riposino
          </h3>
          <div class="flex gap-4 items-center">
            <div class="flex-1">
              <label class="text-xs text-indigo-400 font-bold uppercase mb-1 block">Inizio</label>
              <input type="time" v-model="form.sonno.da" class="w-full rounded-xl border-indigo-200 focus:ring-indigo-300 bg-white p-3 text-indigo-900 shadow-sm">
            </div>
            <span class="text-indigo-300 font-bold text-xl">➜</span>
            <div class="flex-1">
              <label class="text-xs text-indigo-400 font-bold uppercase mb-1 block">Fine</label>
              <input type="time" v-model="form.sonno.a" class="w-full rounded-xl border-indigo-200 focus:ring-indigo-300 bg-white p-3 text-indigo-900 shadow-sm">
            </div>
          </div>
        </section>

        <!-- Sezione Bisogni (Arancione) -->
        <section class="bg-orange-50 rounded-3xl p-6 border border-orange-100 shadow-sm">
          <div class="flex justify-between items-center mb-4">
            <h3 class="font-serif text-xl text-orange-800">🚽 I Cambi</h3>
            <button type="button" @click="addBisogno" class="text-sm bg-white text-orange-500 px-4 py-2 rounded-full shadow-sm font-bold hover:bg-orange-100 transition-colors border border-orange-100">+ Aggiungi</button>
          </div>
          
          <div class="space-y-3">
            <div v-for="(cambio, idx) in form.bisogni" :key="idx" class="flex gap-3 items-center animate-fadeIn">
              <input type="time" v-model="cambio.ora" class="rounded-xl border-orange-200 w-28 p-3 text-orange-900 focus:ring-orange-300 shadow-sm">
              <select v-model="cambio.tipo" class="rounded-xl border-orange-200 flex-1 p-3 text-orange-900 focus:ring-orange-300 bg-white shadow-sm">
                <option value="pipì">Solo Pipì 💧</option>
                <option value="solido">Solido 💩</option>
                <option value="misto">Misto 💩💧</option>
              </select>
              <button type="button" @click="form.bisogni.splice(idx, 1)" class="w-10 h-10 flex items-center justify-center text-red-400 hover:text-red-600 hover:bg-red-50 rounded-full transition-colors font-bold text-xl">
                 &times;
              </button>
            </div>
            <p v-if="form.bisogni.length === 0" class="text-orange-400/60 text-sm italic text-center py-4 border-2 border-dashed border-orange-200 rounded-xl">
                Nessun cambio registrato oggi.
            </p>
          </div>
        </section>

        <!-- Sezione Attività e Umore (Viola) -->
        <section class="bg-violet-50 rounded-3xl p-6 border border-violet-100 shadow-sm">
          <h3 class="font-serif text-xl text-violet-800 mb-6">🎨 Attività & Umore</h3>
          
          <div class="mb-6 p-4 bg-white/50 rounded-2xl border border-violet-100">
            <label class="block text-sm font-bold text-violet-700 mb-3 text-center uppercase tracking-wide">Come è stato oggi?</label>
            <div class="flex justify-center gap-8">
              <label class="cursor-pointer group relative">
                <input type="radio" v-model="form.umore" value="sereno" class="peer hidden">
                <span class="text-5xl filter grayscale peer-checked:grayscale-0 transition-all duration-300 group-hover:scale-110 block">😄</span>
                <span class="absolute -bottom-6 left-1/2 -translate-x-1/2 text-xs font-bold text-violet-600 opacity-0 peer-checked:opacity-100 transition-opacity">Sereno</span>
              </label>

              <label class="cursor-pointer group relative">
                <input type="radio" v-model="form.umore" value="triste" class="peer hidden">
                <span class="text-5xl filter grayscale peer-checked:grayscale-0 transition-all duration-300 group-hover:scale-110 block">😢</span>
                <span class="absolute -bottom-6 left-1/2 -translate-x-1/2 text-xs font-bold text-violet-600 opacity-0 peer-checked:opacity-100 transition-opacity">Triste</span>
              </label>

              <label class="cursor-pointer group relative">
                <input type="radio" v-model="form.umore" value="agitato" class="peer hidden">
                <span class="text-5xl filter grayscale peer-checked:grayscale-0 transition-all duration-300 group-hover:scale-110 block">😡</span>
                <span class="absolute -bottom-6 left-1/2 -translate-x-1/2 text-xs font-bold text-violet-600 opacity-0 peer-checked:opacity-100 transition-opacity">Agitato</span>
              </label>
            </div>
          </div>

          <textarea 
            v-model="form.attivita" 
            placeholder="Descrivi le attività svolte oggi... (Es. Disegno con le dita, canzoncine in giardino)" 
            class="w-full h-32 rounded-2xl border-violet-200 focus:ring-violet-300 p-4 resize-none text-violet-900 placeholder-violet-300 shadow-inner"
          ></textarea>
        </section>

        <!-- Save Button -->
        <div class="fixed bottom-6 right-6 md:static md:flex md:justify-end">
          <button 
            type="submit" 
            :disabled="form.processing"
            class="bg-sky-500 hover:bg-sky-600 text-white font-bold text-lg px-8 py-4 rounded-full shadow-xl shadow-sky-200 transition-all active:scale-95 disabled:opacity-70 disabled:cursor-not-allowed flex items-center gap-2"
          >
            <span v-if="!form.processing">Salva Diario ✨</span>
            <span v-else>Salvataggio...</span>
          </button>
        </div>

      </form>
    </div>
  </AppLayout>
</template>

<style scoped>
/* Utility per nascondere scrollbar ma permettere scroll */
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
.animate-fadeIn {
    animation: fadeIn 0.3s ease-out forwards;
}
</style>