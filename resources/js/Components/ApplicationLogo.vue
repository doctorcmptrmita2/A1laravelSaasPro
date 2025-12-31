<template>
    <div class="flex items-center space-x-3 group cursor-pointer">
        <!-- Professional Logo Icon -->
        <div class="relative">
            <!-- Glow Effect -->
            <div class="absolute inset-0 bg-gradient-to-br from-blue-500 via-purple-500 to-pink-500 rounded-xl blur-md opacity-30 group-hover:opacity-50 transition-opacity duration-300"></div>
            
            <!-- Main Logo Container -->
            <div class="relative bg-gradient-to-br from-blue-600 via-purple-600 to-indigo-700 rounded-xl p-2.5 shadow-xl transform group-hover:scale-105 transition-transform duration-300">
                <!-- Inner Glow -->
                <div class="absolute inset-0 bg-gradient-to-br from-white/20 to-transparent rounded-xl"></div>
                
                <!-- SVG Icon - Modern API Gateway Symbol -->
                <svg 
                    class="relative w-8 h-8 text-white transform group-hover:rotate-12 transition-transform duration-300" 
                    fill="none" 
                    stroke="currentColor" 
                    viewBox="0 0 24 24" 
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <!-- Network/Connection Nodes -->
                    <circle cx="6" cy="6" r="2" stroke-width="2" fill="currentColor" opacity="0.9"/>
                    <circle cx="18" cy="6" r="2" stroke-width="2" fill="currentColor" opacity="0.9"/>
                    <circle cx="12" cy="18" r="2" stroke-width="2" fill="currentColor" opacity="0.9"/>
                    <!-- Connection Lines -->
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 6l4 6m0 0l4-6M12 12v6"/>
                    <!-- Data Flow Arrow -->
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H8" opacity="0.8"/>
                </svg>
            </div>
        </div>
        
        <!-- Logo Text -->
        <div class="flex flex-col">
            <span class="text-xl font-extrabold bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-600 bg-clip-text text-transparent group-hover:from-blue-700 group-hover:via-purple-700 group-hover:to-indigo-700 transition-all duration-300">
                CodexFlow
            </span>
            <span class="text-[10px] font-medium text-gray-500 -mt-1 tracking-wider uppercase">
                AI Gateway
            </span>
        </div>
    </div>
</template>

