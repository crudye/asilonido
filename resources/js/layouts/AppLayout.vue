<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

// Menu di navigazione
const menuItems = [
  { name: 'Bambini', route: '/bambini', icon: '👶', color: 'bg-sky-100 text-sky-700' },
  { name: 'Diario', route: '/diario', icon: '📖', color: 'bg-rose-100 text-rose-700' },
  { name: 'Presenze', route: '/presenze', icon: '✅', color: 'bg-emerald-100 text-emerald-700' },
  { name: 'Mensa', route: '/mensa', icon: '🍎', color: 'bg-amber-100 text-amber-700' },
  { name: 'Bacheca', route: '/avvisi', icon: '📌', color: 'bg-violet-100 text-violet-700' },
];

// Funzione helper per determinare se il link è attivo
// Controlla se l'URL corrente inizia con la rotta specificata
const isUrlActive = (route) => {
  return usePage().url.startsWith(route);
};
</script>

<template>
  <div class="min-h-screen bg-slate-50 flex flex-col md:flex-row font-sans text-slate-600">
    
    <!-- Sidebar Mobile/Desktop -->
    <aside class="w-full md:w-64 bg-white shadow-xl md:h-screen md:fixed z-10 flex flex-col">
      <!-- Logo Header -->
      <div class="p-6 flex items-center justify-center border-b border-slate-100 shrink-0">
        <h1 class="font-serif text-3xl text-slate-700 font-bold tracking-tight select-none">
          Asilo <span class="text-sky-500">Felice</span>
        </h1>
      </div>
      
      <!-- Menu Navigation -->
      <nav class="p-4 space-y-2 flex-1 overflow-y-auto">
        <Link 
          v-for="item in menuItems" 
          :key="item.name" 
          :href="item.route"
          class="flex items-center gap-4 p-3 rounded-2xl transition-all duration-200 hover:bg-slate-50 hover:shadow-sm group"
          :class="{ 'bg-slate-100 font-bold shadow-inner ring-1 ring-slate-200': isUrlActive(item.route) }"
        >
          <!-- Icona con colore dinamico -->
          <div 
            :class="[
              `w-10 h-10 flex items-center justify-center rounded-full shadow-sm transition-transform group-hover:scale-110`,
              item.color
            ]"
          >
            {{ item.icon }}
          </div>
          <!-- Label -->
          <span class="text-lg">{{ item.name }}</span>
        </Link>
      </nav>

      <!-- Footer Sidebar (Opzionale: User profile / Logout) -->
      <div class="p-4 border-t border-slate-100 shrink-0">
        <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50">
          <div class="w-8 h-8 rounded-full bg-slate-300 flex items-center justify-center">👤</div>
          <div class="text-sm">
            <p class="font-bold text-slate-700">Admin</p>
            <p class="text-xs text-slate-400">Esci</p>
          </div>
        </div>
      </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 md:ml-64 p-4 md:p-8 overflow-y-auto h-screen">
      <div class="max-w-7xl mx-auto pb-20">
        <!-- Slot dove Inertia inietterà le Pagine -->
        <slot /> 
      </div>
    </main>

  </div>
</template>