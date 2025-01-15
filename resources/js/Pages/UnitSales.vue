<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    items: Object,
    categories: Object,
    cat: Object,
    filters: Object
})

const form = ref({
    search: props.filters.search,
    date: props.filters.date
})

const date = form.value.date ? new Date(form.value.date) : new Date()
const nextable = ref(date.getTime() < new Date(new Date().setHours(0,0,0,0)).getTime())

watch(form, (data) => {
    nextable.value = new Date(data.date).getTime() < new Date(new Date().setHours(0,0,0,0)).getTime()
    router.get(route('unit.sales', props.cat), data, {
        preserveState: true,
        preserveScroll: true, 
        replace: true,
    })
}, {
    deep: true
})
</script>

<template>
    <Head>
        <title>
            Unit Sales
        </title>
    </Head>

    <div class="max-w-7xl md:flex mx-auto mt-8 px-6 lg:px-8">
        <div class="w-full md:w-1/6 bg-white dark:bg-zinc-900 border dark:border-zinc-700 py-4 rounded-lg h-min mb-6 text-sm">
            <div class="flex justify-between items-center px-4 dark:text-white">
                <span>Categories</span>
            </div>
            <hr class="border-zinc-600 mt-1">
            <div v-for="category in categories" @click.stop="$inertia.get(route('unit.sales', category), form, {
                preserveState: true, preserveScroll: true, replace: true
            })"
                :class="{ 'bg-blue-500 hover:bg-blue-500 font-bold text-white': $page.url.replace('%20', ' ').indexOf(category.name) > -1 }"
                class="dark:text-white px-4 py-2 duration-200 ease-in-out flex justify-between group cursor-pointer">
                    <span>{{ category.name }}</span>
            </div>
        </div>
        <div class="w-full md:w-5/6 md:px-4 dark:text-white">
            <div class="flex justify-between items-center mb-4">
                <div class="flex space-x-2">
                    <i @click="form.date = new Date(date.setDate(date.getDate() - 1)).toLocaleDateString()"
                        class='bx bxs-chevron-left dark:text-white hover:bg-white hover:text-zinc-900 w-6 h-6 inline-flex justify-center items-center rounded-full border dark:border-white'></i>
                    <p class="dark:text-white font-semibold">{{ date.toWordFormat() }}</p>
                    <i v-if="nextable"
                        @click="form.date = new Date(date.setDate(date.getDate() + 1)).toLocaleDateString()"
                        class='bx bxs-chevron-right dark:text-white hover:bg-white hover:text-zinc-900 w-6 h-6 inline-flex justify-center items-center rounded-full border dark:border-white'></i>
                </div>

                <div>
                    <label class="relative block">
                    <input v-model="form.search"
                        class="[appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none duration-300 ease-in-out placeholder:text-xs placeholder:text-zinc-400 dark:text-white block bg-shite dark:bg-zinc-900 w-full border-slate-300 dark:border-slate-300/20 rounded-md py-2 pl-3 pr-9 shadow-sm focus:border-indigo-300 focus:ring-indigo-200 focus:ring focus:ring-opacity-50"
                        placeholder="Search" type="text" name="search" />
                    <i
                        class='bx bx-search text-black/40 dark:text-white/40 absolute text-xl inset-y-0 right-0 flex items-center pr-3'></i>
                </label>
                </div>
            </div>

            <table class="w-full bg-white dark:bg-zinc-900 rounded-lg border dark:border-zinc-700 shadow-sm">
                <thead class="text-left">
                    <tr>
                        <th class="px-4 py-2">Item</th>
                        <th class="px-4 py-2">Total quantity of sales</th>
                        <th class="px-4 py-2 text-right">Gross</th>
                        <th class="px-4 py-2 text-right">Cost</th>
                        <th class="px-4 py-2 text-right">Net</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="dark:odd:bg-zinc-800" v-for="item in items">
                        <td class="px-4 py-2">{{ item.name }}</td>
                        <td class="px-4 py-2">{{ item.total_quantity }}</td>
                        <td class="px-4 py-2 text-right">{{ (item.total_quantity * item.price).amountFormat() ?? 0 }}</td>
                        <td class="px-4 py-2 text-right">{{ (item.total_quantity * item.cost).amountFormat() ?? 0 }}</td>
                        <td class="px-4 py-2 text-right">{{ ((item.total_quantity * item.price) - (item.total_quantity * item.cost)).amountFormat() ?? 0 }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>