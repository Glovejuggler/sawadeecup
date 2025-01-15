<script setup>
import { onMounted, ref, watch } from 'vue';

const props = defineProps({
    data: {
        type: Object
    },
    id: {
        type: Number
    }
})

const emit = defineEmits(['remove', 'finish'])

const order = ref(null)
const isFetching = ref(false)

onMounted(() => {
    if (props.id) {
        isFetching.value = true
        axios.get(route('order.get', props.id))
            .then((res) => {
                res.data.items.forEach(i => i.done = false)
                order.value = res.data
            })
            .finally(() => {
                isFetching.value = false
            })
    }
})

const removeOrder = () => {
    emit('remove')
}

const finishOrder = () => {
    emit('finish')
}
</script>

<template>
    <div v-if="!isFetching && order"
        class="bg-zinc-800 rounded-lg p-4 ease-in-out duration-200 transition-all relative text-white h-min">

        <div class="flex justify-between">
            <div>
                <p class="text-xs">{{ Intl.DateTimeFormat("en-US", {
            month: 'long',
            day: 'numeric',
            year: 'numeric',
            hour: 'numeric',
            minute: 'numeric'
        }).format(new Date(order.created_at)) }}</p>
                <p v-if="order.name">Customer: {{ order.name }}</p>

            </div>

            <span class="rounded-full px-5 border text-lg font-bold h-min" :class="order.type === 'Dine-in' ? 'bg-green-500 border-green-950 text-green-950' : 'bg-red-500 border-red-950 text-red-950'">{{ order.type }}</span>
        </div>


        <p class="rounded-lg p-2 bg-zinc-700 mt-2" :class="{'opacity-50 line-through': item.done}" @click="item.done = !item.done" v-for="item in order.items">
            {{ `${item.name} x${item.quantity}` }}
        </p>

        
        <div class="my-4 rounded-md p-3 bg-zinc-900 text-zinc-200" v-if="order.note">
            {{ order.note }}
        </div>

        <div class="flex justify-between space-x-2 mt-2">
            <button @click="removeOrder" class="p-2 bg-red-500 rounded-2xl text-white text-xs w-full">Cancel</button>
            <button @click="finishOrder" class="p-2 bg-blue-500 rounded-2xl text-white text-xs w-full disabled:opacity-25 disabled:cursor-not-allowed" :disabled="!order.items.every(i => i.done)">Done</button>
        </div>
    </div>

    <div v-else class="bg-zinc-800 rounded-lg p-4 ease-in-out duration-200 transition-all text-white h-min">
        <p class="h-3 w-32 rounded-full bg-zinc-600 mb-2 animate-pulse"></p>
        <p class="h-28 rounded-lg bg-zinc-600 animate-pulse"></p>
        <div class="flex justify-between space-x-2 mt-2">
            <div class="h-8 rounded-2xl bg-zinc-600 animate-pulse w-full"></div>
            <div class="h-8 rounded-2xl bg-zinc-600 animate-pulse w-full"></div>
        </div>
    </div>
</template>