<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '../layouts/AppLayout.vue';

const props = defineProps({
  bambini: Array,
  classi: Array
});

const showModal = ref(false);

const form = useForm({
  nome: '',
  cognome: '',
  data_nascita: '',
  sesso: 'M',
  codice_fiscale: '',
  classe_id: '',
  allergie: []
});

// SOLUZIONE: Definiamo lo stile qui come stringa, così evitiamo @apply e l'errore di build
const inputClasses = "w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-sky-200 focus:border-sky-300 transition-all placeholder-slate-400 text-slate-600";

const saveBambino = () => {
  form.post('/bambini', {
    onSuccess: () => {
      showModal.value = false;
      form.reset();
    },
  });
};
</script>

<template>
  <AppLayout>
    <div class="flex justify-between items-center mb-8">
      <div>
        <h2 class="font-serif text-3xl text-slate-800 mb-1">I Nostri Bambini</h2>
        <p class="text-slate-500">Gestione anagrafica e classi</p>
      </div>
      <button 
        @click="showModal = true"
        class="bg-sky-400 hover:bg-sky-500 text-white px-6 py-3 rounded-full shadow-lg shadow-sky-200 transition-all font-bold flex items-center gap-2"
      >
        <span>+</span> Nuovo Iscritto
      </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div 
        v-for="bambino in bambini" 
        :key="bambino._id"
        class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow relative overflow-hidden"
      >
        <div class="absolute top-0 left-0 w-full h-2 bg-gradient-to-r from-sky-200 to-rose-200"></div>
        
        <div class="flex items-center gap-4 mb-4">
          <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center text-2xl border-2 border-white shadow-sm">
            {{ bambino.sesso === 'M' ? '👦' : '👧' }}
          </div>
          <div>
            <h3 class="font-serif text-xl font-bold text-slate-700">
              {{ bambino.nome }} {{ bambino.cognome }}
            </h3>
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 bg-slate-50 px-2 py-1 rounded-lg">
              {{ bambino.classe?.nome || 'Nessuna classe' }}
            </span>
          </div>
        </div>

        <div class="space-y-2 text-sm text-slate-600">
          <p>🎂 {{ new Date(bambino.data_nascita).toLocaleDateString('it-IT') }}</p>
          <p v-if="bambino.allergie && bambino.allergie.length > 0" class="text-rose-500 font-bold flex gap-2">
            ⚠️ {{ bambino.allergie.join(', ') }}
          </p>
        </div>
      </div>
    </div>

    <div v-if="showModal" class="fixed inset-0 bg-black/30 backdrop-blur-sm flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-3xl p-8 max-w-lg w-full shadow-2xl">
        <h3 class="font-serif text-2xl mb-6 text-slate-800">Nuova Iscrizione</h3>
        
        <form @submit.prevent="saveBambino" class="space-y-4">
          
          <div class="grid grid-cols-2 gap-4">
            <div>
              <input v-model="form.nome" placeholder="Nome" :class="inputClasses" required />
              <div v-if="form.errors.nome" class="text-xs text-rose-500 mt-1">{{ form.errors.nome }}</div>
            </div>
            <div>
              <input v-model="form.cognome" placeholder="Cognome" :class="inputClasses" required />
              <div v-if="form.errors.cognome" class="text-xs text-rose-500 mt-1">{{ form.errors.cognome }}</div>
            </div>
          </div>
          
          <div>
            <select v-model="form.classe_id" :class="inputClasses" required>
              <option value="" disabled>Seleziona la Classe</option>
              <option v-for="classe in classi" :key="classe._id" :value="classe._id">
                {{ classe.nome }}
              </option>
            </select>
            <div v-if="form.errors.classe_id" class="text-xs text-rose-500 mt-1">{{ form.errors.classe_id }}</div>
          </div>

          <input type="date" v-model="form.data_nascita" :class="inputClasses" required />
          <input v-model="form.codice_fiscale" placeholder="Codice Fiscale" :class="inputClasses" />
          <div v-if="form.errors.codice_fiscale" class="text-xs text-rose-500 mt-1">{{ form.errors.codice_fiscale }}</div>

          <div class="flex gap-4">
             <label class="flex items-center gap-2 cursor-pointer bg-blue-50 px-4 py-2 rounded-xl border border-blue-100 flex-1 justify-center">
                <input type="radio" v-model="form.sesso" value="M" class="text-blue-500 focus:ring-blue-300"> 
                <span class="text-blue-700 font-bold">Maschio</span>
             </label>
             <label class="flex items-center gap-2 cursor-pointer bg-pink-50 px-4 py-2 rounded-xl border border-pink-100 flex-1 justify-center">
                <input type="radio" v-model="form.sesso" value="F" class="text-pink-500 focus:ring-pink-300"> 
                <span class="text-pink-700 font-bold">Femmina</span>
             </label>
          </div>

          <div class="flex justify-end gap-3 mt-6">
            <button type="button" @click="showModal = false" class="px-6 py-2 rounded-full text-slate-500 hover:bg-slate-100 font-bold transition-colors">
              Annulla
            </button>
            <button 
              type="submit" 
              :disabled="form.processing"
              class="px-8 py-2 rounded-full bg-sky-400 text-white font-bold hover:bg-sky-500 shadow-md transition-all disabled:opacity-50"
            >
              {{ form.processing ? 'Salvataggio...' : 'Salva' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>