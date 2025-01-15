<script setup>
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    costing: Object,
    total: Number
})
</script>

<template>
<Head>
    <title>
        Inventory Costing
    </title>
</Head>

<div class="max-w-2xl mx-auto py-8">
    <div class="border dark:border-zinc-700 rounded-lg">
        <table class="w-full table bg-white dark:bg-zinc-900 dark:text-white rounded-lg overflow-hidden">
            <thead class="text-left">
                <tr>
                    <th class="px-4 py-2">Inventory</th>
                    <th class="px-4 py-2">Cost</th>
                </tr>
            </thead>
            <tbody>
                <tr class="dark:odd:bg-zinc-800 odd:bg-zinc-200" v-for="cost in costing">
                    <td class="px-4 py-2">{{ cost.name }}</td>
                    <td class="px-4 py-2">{{ (cost.total).amountFormat() }}</td>
                </tr>
                <tr class="font-bold dark:odd:bg-zinc-800 odd:bg-zinc-200">
                    <td class="px-4 py-2">Total</td>
                    <td class="px-4 py-2">{{ total.amountFormat() }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
</template>