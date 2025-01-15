<script setup>
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    items: Number,
    sold: Number,
})
</script>

<template>

    <Head title="Dashboard" />

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-6 gap-2">
                <div
                    class="bg-gradient-to-br from-green-400 to-blue-500 overflow-hidden shadow-sm rounded-lg p-4 relative group">
                    <p class="text-white text-sm">Products</p>
                    <p class="text-white font-bold text-4xl text-end">{{ items }}</p>
                    <i
                        class='bx bx-food-menu absolute -bottom-4 left-4 text-7xl text-white/40 group-hover:scale-110 duration-300 ease-in-out'></i>
                </div>
    
                <div @click="$inertia.get(route('pos'))"
                    class="bg-gradient-to-br from-indigo-800 to-violet-500 overflow-hidden shadow-sm rounded-lg p-4 relative group flex justify-center items-center">
                    <i
                        class="bx bx-calculator text-7xl absolute -bottom-4 left-4 text-white/40 group-hover:scale-110 duration-300 ease-in-out"></i>
                    <p class="font-black text-white text-4xl z-30">POS</p>
                </div>
    
                <div @click="$inertia.get(route('kitchen'))"
                    class="bg-gradient-to-br from-pink-500 to-yellow-500 overflow-hidden shadow-sm rounded-lg p-4 relative group flex justify-center items-center">
                    <i
                        class="bx bx-bowl-hot text-7xl absolute -bottom-4 left-4 text-white/40 group-hover:scale-110 duration-300 ease-in-out"></i>
                    <p class="font-black text-white text-4xl z-30">Kitchen</p>
                </div>
            </div>

            <p class="mt-4 font-bold uppercase dark:text-white">Stats</p>
            <div class="grid lg:grid-cols-6 gap-2">
                <div class="bg-gradient-to-br from-violet-900 to-sky-400 overflow-hidden shadow-sm rounded-lg p-2 md:p-4 relative group flex justify-center items-center">
                    <div class="flex justify-center items-center flex-col">
                        <p class="font-bold text-white text-xs lg:text-3xl">{{ sold }}</p>
                        <i class="text-white text-sm font-bold block lg:hidden bx bx-money-withdraw"></i>
                        <p class="text-white text-xs font-bold lg:block hidden">TOTAL SOLD</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
