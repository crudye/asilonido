<script setup>
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '../layouts/AppLayout.vue';

// Props: 'bambino' sarà null in creazione, popolato in modifica
const props = defineProps({
  bambino: Object,
  classi: Array
});
console.log(props.classi);
// Determiniamo se siamo in modalità Modifica
const isEditing = !!props.bambino;

// Inizializziamo il form con i dati del bambino (se presenti) o default vuoti
const form = useForm({
  nome: props.bambino?.nome || '',
  cognome: props.bambino?.cognome || '',
  codice_fiscale: props.bambino?.codice_fiscale || '',
  data_nascita: props.bambino?.data_nascita ? props.bambino.data_nascita.split('T')[0] : '', // Format YYYY-MM-DD
  sesso: props.bambino?.sesso || 'M',
  classe_id: props.bambino?.classe_id || '',
  allergie: props.bambino?.allergie || [],
  deleghe_ritiro: props.bambino?.deleghe_ritiro || [],
  
  // Campo temporaneo per aggiungere una nuova allergia
  nuovaAllergia: '' 
});

// Gestione aggiunta allergie UI
const addAllergia = () => {
  if (form.nuovaAllergia.trim()) {
    form.allergie.push(form.nuovaAllergia.trim());
    form.nuovaAllergia = '';
  }
};

const removeAllergia = (index) => {
  form.allergie.splice(index, 1);
};

// Submit unico
const submit = () => {
  if (isEditing) {
    // Invia PUT per aggiornare
    // Nota: assicurati che props.bambino abbia l'id (stringa) o _id corretto
    const id = props.bambino.id || props.bambino._id;
    form.put(`/bambini/${id}`);
  } else {
    // Invia POST per creare
    form.post('/bambini');
  }
};
</script>

<template>
  <AppLayout>
    <div class="w-full mx-auto py-10">
      
      <!-- Header con tasto Indietro -->
      <div class="flex items-center justify-between mb-8">
        <div>
           <Link href="/bambini" class="text-sm text-slate-400 hover:text-sky-500 font-bold mb-2 inline-block">← Torna alla lista</Link>
           <h2 class="font-serif text-3xl text-slate-800">
             {{ isEditing ? `Modifica ${form.nome}` : 'Nuova Iscrizione' }}
           </h2>
           <p class="text-slate-500">Compila la scheda anagrafica completa.</p>
        </div>
      </div>

      <div class="bg-white rounded-3xl shadow-xl p-8 border border-slate-100">
        <form @submit.prevent="submit" class="space-y-8">
          
          <!-- Sezione 1: Dati Personali -->
          <div>
            <h3 class="font-serif text-xl text-sky-700 mb-4 border-b border-sky-100 pb-2">Dati Personali</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              
              <div class="space-y-1">
                <label class="text-sm font-bold text-slate-600">Nome</label>
                <input 
                  v-model="form.nome" 
                  type="text" 
                  class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-200 focus:border-sky-300 transition-all text-slate-700 placeholder-slate-400" 
                  placeholder="Es. Mario" 
                />
                <div v-if="form.errors.nome" class="text-rose-500 text-xs">{{ form.errors.nome }}</div>
              </div>

              <div class="space-y-1">
                <label class="text-sm font-bold text-slate-600">Cognome</label>
                <input 
                  v-model="form.cognome" 
                  type="text" 
                  class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-200 focus:border-sky-300 transition-all text-slate-700 placeholder-slate-400" 
                  placeholder="Es. Rossi" 
                />
                <div v-if="form.errors.cognome" class="text-rose-500 text-xs">{{ form.errors.cognome }}</div>
              </div>

              <div class="space-y-1">
                <label class="text-sm font-bold text-slate-600">Codice Fiscale</label>
                <input 
                  v-model="form.codice_fiscale" 
                  type="text" 
                  class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-200 focus:border-sky-300 transition-all text-slate-700 placeholder-slate-400 uppercase" 
                  placeholder="RSSMRA..." 
                />
                <div v-if="form.errors.codice_fiscale" class="text-rose-500 text-xs">{{ form.errors.codice_fiscale }}</div>
              </div>

              <div class="space-y-1">
                <label class="text-sm font-bold text-slate-600">Data di Nascita</label>
                <input 
                  v-model="form.data_nascita" 
                  type="date" 
                  class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-200 focus:border-sky-300 transition-all text-slate-700 placeholder-slate-400" 
                />
                <div v-if="form.errors.data_nascita" class="text-rose-500 text-xs">{{ form.errors.data_nascita }}</div>
              </div>

              <div class="space-y-1">
                <label class="text-sm font-bold text-slate-600">Sesso</label>
                <div class="flex gap-4 mt-2">
                   <label class="flex-1 flex items-center justify-center gap-2 cursor-pointer p-3 rounded-xl border transition-all" 
                          :class="form.sesso === 'M' ? 'bg-blue-50 border-blue-300 text-blue-700' : 'bg-slate-50 border-slate-200 text-slate-400'">
                      <input type="radio" v-model="form.sesso" value="M" class="hidden"> 
                      <span>Maschio 👦</span>
                   </label>
                   <label class="flex-1 flex items-center justify-center gap-2 cursor-pointer p-3 rounded-xl border transition-all"
                          :class="form.sesso === 'F' ? 'bg-pink-50 border-pink-300 text-pink-700' : 'bg-slate-50 border-slate-200 text-slate-400'">
                      <input type="radio" v-model="form.sesso" value="F" class="hidden"> 
                      <span>Femmina 👧</span>
                   </label>
                </div>
              </div>

              <div class="space-y-1">
                <label class="text-sm font-bold text-slate-600">Assegna Classe</label>
                <select 
                  v-model="form.classe_id" 
                  class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-200 focus:border-sky-300 transition-all text-slate-700 placeholder-slate-400"
                >
                  <option value="" disabled>Seleziona...</option>
                  <option v-for="c in classi" :key="c.id || c._id" :value="c.id || c._id">
                    {{ c.nome }}
                  </option>
                </select>
                <div v-if="form.errors.classe_id" class="text-rose-500 text-xs">{{ form.errors.classe_id }}</div>
              </div>

            </div>
          </div>

          <!-- Sezione 2: Salute e Note -->
          <div>
            <h3 class="font-serif text-xl text-rose-700 mb-4 border-b border-rose-100 pb-2">Salute & Allergie</h3>
            
            <div class="bg-rose-50 p-6 rounded-2xl border border-rose-100">
               <label class="text-sm font-bold text-rose-800 mb-2 block">Lista Allergie / Intolleranze</label>
               
               <div class="flex gap-2 mb-4">
                 <input 
                    v-model="form.nuovaAllergia" 
                    @keydown.enter.prevent="addAllergia"
                    type="text" 
                    placeholder="Es. Glutine, Lattosio..." 
                    class="flex-1 rounded-xl border-rose-200 focus:ring-rose-300 px-4 py-2"
                 />
                 <button type="button" @click="addAllergia" class="bg-rose-400 text-white px-4 rounded-xl font-bold hover:bg-rose-500">+</button>
               </div>

               <div class="flex flex-wrap gap-2">
                 <span v-for="(allergia, idx) in form.allergie" :key="idx" class="bg-white text-rose-600 px-3 py-1 rounded-full text-sm font-bold border border-rose-200 flex items-center gap-2 shadow-sm">
                    ⚠️ {{ allergia }}
                    <button type="button" @click="removeAllergia(idx)" class="text-rose-300 hover:text-rose-600">x</button>
                 </span>
                 <span v-if="form.allergie.length === 0" class="text-rose-400 text-sm italic">Nessuna allergia segnalata.</span>
               </div>
            </div>
          </div>

          <!-- Azioni -->
          <div class="flex items-center justify-end gap-4 pt-6 border-t border-slate-100">
            <Link href="/bambini" class="px-6 py-3 rounded-full text-slate-500 hover:bg-slate-100 font-bold transition-colors">
              Annulla
            </Link>
            <button 
              type="submit" 
              :disabled="form.processing"
              class="px-8 py-3 rounded-full bg-sky-500 text-white font-bold text-lg hover:bg-sky-600 shadow-lg shadow-sky-200 transition-all active:scale-95 disabled:opacity-50"
            >
              {{ form.processing ? 'Salvataggio...' : (isEditing ? 'Aggiorna Scheda' : 'Iscrivi Bambino') }}
            </button>
          </div>

        </form>
      </div>
    </div>
  </AppLayout>
</template>