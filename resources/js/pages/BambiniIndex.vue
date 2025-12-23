<script setup>
import { ref } from 'vue';
import { useForm, Link } from '@inertiajs/vue3';
import AppLayout from '../layouts/AppLayout.vue';

const props = defineProps({
  bambini: Array,
  classi: Array
});
console.log(props.bambini);
// SOLUZIONE: Definiamo lo stile qui come stringa, così evitiamo @apply e l'errore di build
const inputClasses = "w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 focus:outline-none focus:ring-2 focus:ring-sky-200 focus:border-sky-300 transition-all placeholder-slate-400 text-slate-600";

</script>

<template>
  <AppLayout>
    <div class="flex justify-between items-center mb-8">
      <div>
        <h2 class="font-serif text-3xl text-slate-800 mb-1">I Nostri Bambini</h2>
        <p class="text-slate-500">Gestione anagrafica e classi</p>
      </div>
      <Link 
        href="/bambini/crea"
        class="bg-sky-400 hover:bg-sky-500 text-white px-6 py-3 rounded-full shadow-lg shadow-sky-200 transition-all font-bold flex items-center gap-2"
      >
        <span>+</span> Nuovo Iscritto
    </Link>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div 
        v-for="bambino in bambini" 
        :key="bambino._id"
        class="bg-white p-6 rounded-3xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow relative overflow-hidden"
      >
      <Link 
          :href="`/bambini/${bambino.id}`"           
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
        </Link> 
      </div>
    </div>

  </AppLayout>
</template>