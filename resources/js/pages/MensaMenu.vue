<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import AppLayout from '../layouts/AppLayout.vue';

const props = defineProps({
  menuSettimana: Object, // Keyed by 'data_selezionata' (es. {'2024-05-27': {...}})
  inizioSettimana: String
});

// Generiamo i 5 giorni (Lunedì - Venerdì) basandoci sulla data di inizio
const giorniLavorativi = computed(() => {
  const giorni = [];
  const start = new Date(props.inizioSettimana);
  
  for (let i = 0; i < 5; i++) {
    const dataCorrente = new Date(start);
    dataCorrente.setDate(start.getDate() + i);
    
    giorni.push({
      dataFormattata: dataCorrente.toISOString().split('T')[0], // YYYY-MM-DD
      nomeGiorno: dataCorrente.toLocaleDateString('it-IT', { weekday: 'long' }),
      numeroGiorno: dataCorrente.getDate(),
      mese: dataCorrente.toLocaleDateString('it-IT', { month: 'short' })
    });
  }
  return giorni;
});

// Stato per la UI (Giorno attualmente selezionato per la modifica)
const selectedDate = ref(giorniLavorativi.value[0].dataFormattata);

// Input temporaneo per gli allergeni (testo separato da virgola)
const allergeniTesto = ref('');

// Setup Form Inertia
const form = useForm({
  data_selezionata: selectedDate.value,
  piatti: {
    primo: '',
    secondo: '',
    contorno: '',
    merenda: ''
  },
  allergeni: [] // Verrà popolato dalla stringa prima del submit
});

// Quando clicchiamo su un giorno, popoliamo il form con i dati esistenti
watch(selectedDate, (nuovaData) => {
  form.clearErrors();
  const menuEsistente = props.menuSettimana[nuovaData];

  if (menuEsistente) {
    // MODIFICA
    form.data_selezionata = nuovaData;
    form.piatti = menuEsistente.piatti || { primo: '', secondo: '', contorno: '', merenda: '' };
    form.allergeni = menuEsistente.allergeni || [];
    allergeniTesto.value = form.allergeni.join(', '); // Convertiamo array in stringa per l'input
  } else {
    // CREAZIONE NUOVO
    form.reset('piatti', 'allergeni');
    form.piatti = { primo: '', secondo: '', contorno: '', merenda: '' };
    form.data_selezionata = nuovaData;
    allergeniTesto.value = '';
  }
}, { immediate: true });

// Navigazione Settimane
const cambiaSettimana = (offsetGiorni) => {
  const currentStart = new Date(props.inizioSettimana);
  currentStart.setDate(currentStart.getDate() + offsetGiorni);
  const nuovaData = currentStart.toISOString().split('T')[0];
  
  router.get('/mensa', { inizio_settimana: nuovaData }, { preserveState: true });
};

// Salvataggio
const salvaMenu = () => {
  // Trasformiamo la stringa degli allergeni in un array pulito prima di inviare
  const arrayAllergeni = allergeniTesto.value
    .split(',')
    .map(a => a.trim())
    .filter(a => a.length > 0);

  form.transform((data) => ({
    ...data,
    allergeni: arrayAllergeni
  })).post('/mensa', {
    preserveScroll: true,
    onSuccess: () => {
        // Feedback gestito da Inertia
    }
  });
};

// Eliminazione
const eliminaMenu = () => {
  const menuEsistente = props.menuSettimana[selectedDate.value];
  if (menuEsistente && confirm('Vuoi svuotare il menù di questo giorno?')) {
    router.delete(`/mensa/${menuEsistente.id}`, { preserveScroll: true });
  }
};
</script>

<template>
  <AppLayout>
    <div class="max-w-6xl mx-auto pb-20">

      <!-- HEADER & CONTROLLI SETTIMANA -->
      <div class="flex justify-between items-center mb-8 bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
        <div>
          <h2 class="font-serif text-3xl text-slate-800">Menu Mensa</h2>
          <p class="text-slate-500">Pianifica i pasti della settimana</p>
        </div>
        
        <div class="flex items-center gap-4 bg-slate-50 p-2 rounded-2xl border border-slate-200">
          <button @click="cambiaSettimana(-7)" class="p-2 hover:bg-white rounded-xl transition-all shadow-sm text-slate-600 font-bold">&larr; Prec</button>
          <div class="font-bold text-sky-700 min-w-[120px] text-center uppercase tracking-wider text-sm">
             Dal {{ giorniLavorativi[0].numeroGiorno }} {{ giorniLavorativi[0].mese }}
          </div>
          <button @click="cambiaSettimana(7)" class="p-2 hover:bg-white rounded-xl transition-all shadow-sm text-slate-600 font-bold">Pros &rarr;</button>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- COLONNA SINISTRA: Calendario Settimanale -->
        <div class="lg:col-span-2 space-y-4">
          <div 
            v-for="giorno in giorniLavorativi" 
            :key="giorno.dataFormattata"
            @click="selectedDate = giorno.dataFormattata"
            class="p-4 rounded-3xl border-2 transition-all cursor-pointer flex gap-6 items-center"
            :class="selectedDate === giorno.dataFormattata ? 'border-amber-400 bg-amber-50 shadow-md transform scale-[1.01]' : 'border-transparent bg-white shadow-sm hover:border-amber-200'"
          >
            <!-- Data Badge -->
            <div class="flex flex-col items-center justify-center w-20 h-20 rounded-2xl shrink-0"
                 :class="selectedDate === giorno.dataFormattata ? 'bg-amber-400 text-white' : 'bg-slate-100 text-slate-500'">
              <span class="text-xs font-bold uppercase">{{ giorno.nomeGiorno }}</span>
              <span class="text-3xl font-serif font-black">{{ giorno.numeroGiorno }}</span>
            </div>

            <!-- Anteprima Menu -->
            <div class="flex-1">
              <div v-if="menuSettimana[giorno.dataFormattata]" class="space-y-1">
                <p v-if="menuSettimana[giorno.dataFormattata].piatti.primo" class="text-sm font-bold text-slate-700">🍝 {{ menuSettimana[giorno.dataFormattata].piatti.primo }}</p>
                <p v-if="menuSettimana[giorno.dataFormattata].piatti.secondo" class="text-sm font-bold text-slate-700">🍗 {{ menuSettimana[giorno.dataFormattata].piatti.secondo }}</p>
                
                <div v-if="menuSettimana[giorno.dataFormattata].allergeni?.length > 0" class="flex gap-2 mt-2">
                  <span class="text-[10px] bg-rose-100 text-rose-600 px-2 py-0.5 rounded uppercase font-bold">⚠️ Allergeni presenti</span>
                </div>
              </div>
              <div v-else class="text-slate-400 italic text-sm">
                Nessun menù inserito. Clicca per compilare.
              </div>
            </div>
          </div>
        </div>

        <!-- COLONNA DESTRA: Form di Inserimento -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-3xl p-6 shadow-xl border border-slate-100 sticky top-8">
            
            <h3 class="font-serif text-2xl text-amber-800 mb-2 capitalize">
              {{ new Date(selectedDate).toLocaleDateString('it-IT', { weekday: 'long', day: 'numeric', month: 'long' }) }}
            </h3>
            <p class="text-sm text-slate-500 mb-6 pb-4 border-b border-slate-100">Compila il menù del giorno</p>

            <form @submit.prevent="salvaMenu" class="space-y-4">
              
              <!-- Piatti -->
              <div class="space-y-2">
                <label class="text-xs font-bold text-amber-600 uppercase">🍝 Primo Piatto</label>
                <input v-model="form.piatti.primo" type="text" placeholder="Es. Pasta al pomodoro" class="w-full rounded-xl border-slate-200 focus:ring-amber-200 text-slate-700" />
              </div>

              <div class="space-y-2">
                <label class="text-xs font-bold text-amber-600 uppercase">🍗 Secondo Piatto</label>
                <input v-model="form.piatti.secondo" type="text" placeholder="Es. Polpette di verdure" class="w-full rounded-xl border-slate-200 focus:ring-amber-200 text-slate-700" />
              </div>

              <div class="space-y-2">
                <label class="text-xs font-bold text-amber-600 uppercase">🥗 Contorno</label>
                <input v-model="form.piatti.contorno" type="text" placeholder="Es. Carote julienne" class="w-full rounded-xl border-slate-200 focus:ring-amber-200 text-slate-700" />
              </div>

              <div class="space-y-2">
                <label class="text-xs font-bold text-amber-600 uppercase">🍎 Merenda</label>
                <input v-model="form.piatti.merenda" type="text" placeholder="Es. Frutta fresca di stagione" class="w-full rounded-xl border-slate-200 focus:ring-amber-200 text-slate-700" />
              </div>

              <!-- Allergeni -->
              <div class="space-y-2 pt-4 border-t border-slate-100">
                <label class="text-xs font-bold text-rose-600 uppercase flex items-center gap-2">⚠️ Allergeni del giorno</label>
                <input 
                  v-model="allergeniTesto" 
                  type="text" 
                  placeholder="Es. Glutine, Lattosio, Uova (separati da virgola)" 
                  class="w-full rounded-xl border-rose-200 focus:ring-rose-200 text-slate-700 text-sm" 
                />
              </div>

              <!-- Pulsanti -->
              <div class="pt-6 space-y-3">
                <button 
                  type="submit" 
                  :disabled="form.processing"
                  class="w-full bg-amber-400 hover:bg-amber-500 text-white font-bold py-3 rounded-xl shadow-lg shadow-amber-200 transition-all disabled:opacity-50"
                >
                  {{ form.processing ? 'Salvataggio...' : 'Salva Menù' }}
                </button>

                <!-- Tasto Elimina visibile solo se il menu esiste già -->
                <button 
                  v-if="menuSettimana[selectedDate]" 
                  type="button" 
                  @click="eliminaMenu"
                  class="w-full bg-white border-2 border-rose-100 text-rose-500 hover:bg-rose-50 font-bold py-3 rounded-xl transition-all"
                >
                  Svuota Menù
                </button>
              </div>

            </form>

          </div>
        </div>

      </div>
    </div>
  </AppLayout>
</template>