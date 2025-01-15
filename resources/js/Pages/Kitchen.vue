<script setup>
import { ref, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import OrderCard from '@/Components/OrderCard.vue';
import Modal from '@/Components/Modal.vue';

defineOptions({
    layout: null
})

const getRandomInt = (min, max) => {
    const minCeiled = Math.ceil(min);
    const maxFloored = Math.floor(max);
    return Math.floor(Math.random() * (maxFloored - minCeiled + 1) + minCeiled); 
}

const empty = ref(getRandomInt(1, 14));

const props = defineProps({
    orders: Object
})

const ordersData = ref(props.orders)

watch(ordersData.value, () => {
    if (ordersData.value.length === 0) empty.value = getRandomInt(1, 14)
})

const notif = new Audio('../genshin_mail.mp3')
Echo.private('kitchen')
    .listen('OrderPlaced', (e) => {
        if (ordersData.value) {
            ordersData.value.push(e.order)
        } else {
            ordersData.value = [e.order]
        }
        notif.play()
    })
    .listen('OrderDone', (e) => {
        ordersData.value.splice(ordersData.value.findIndex(o => o == e.order), 1)
    })

const selectedOrder = ref({
    index: '',
    order: '',
})

const showRemoveOrderModal = ref(false)
const showFinishOrderModal = ref(false)

const confirmRemoveOrder = (index, order) => {
    selectedOrder.value.index = index
    selectedOrder.value.order = order
    showRemoveOrderModal.value = true
}

const confirmFinishOrder = (index, order) => {
    selectedOrder.value.index = index
    selectedOrder.value.order = order
    showFinishOrderModal.value = true
}

const closeModals = () => {
    showRemoveOrderModal.value = false
    showFinishOrderModal.value = false
}

const removeOrder = () => {
    // ordersData.value.splice(selectedOrder.value.index, 1)
    closeModals()

    axios.delete(route('order.cancel', selectedOrder.value.order)).then((e) => console.log(e.data))
}

const finishOrder = () => {
    // ordersData.value.splice(selectedOrder.value.index, 1)
    closeModals()

    axios.delete(route('order.done', selectedOrder.value.order)).then((e) => console.log(e.data))
}

const beforeLeave = (el) => {
    const { marginLeft, marginTop, width, height } = window.getComputedStyle(el)

    el.style.left = `${el.offsetLeft - parseFloat(marginLeft, 10)}px`
    el.style.top = `${el.offsetTop - parseFloat(marginTop, 10)}px`
    el.style.width = width
    el.style.height = height
}
</script>

<template>

    <Head>
        <title>
            Kitchen
        </title>
    </Head>

    <div class="bg-zinc-950 min-h-screen">
        <TransitionGroup v-if="ordersData.length" tag="div" name="list" @before-leave="beforeLeave"
            class="grid sm:grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-6 p-4">

            <div v-for="(order, index) in ordersData" :key="order">
                <OrderCard :id="order" :key="order" @remove="confirmRemoveOrder(index, order)" @finish="confirmFinishOrder(index, order)" />
            </div>

        </TransitionGroup>

        <div class="min-h-screen w-full flex justify-center items-center text-zinc-400" v-else>
            <div class="flex flex-col">
                <img class="max-w-32 max-h-32" :src="`../empty/${empty}.png`" alt="">
                <p class="text-zinc-400">Nothing in here...</p>
            </div>
        </div>
    </div>

    <!-- Cancel Order Confirmation -->
    <Modal :show="showRemoveOrderModal" @close="closeModals" max-width="md">
        <div class="p-6 bg-zinc-800 text-white">
            <b>Confirmation</b>
            <p class="mt-2">Are you sure you want to cancel this order?</p>
            <p class="text-sm">This action cannot be undone.</p>
            <div class="flex justify-end space-x-4">
                <button @click="closeModals" class="hover:underline">No</button>
                <button @click="removeOrder" class="bg-red-500 hover:bg-red-700 active:bg-red-900 duration-200 ease-in-out px-2 py-1 rounded-lg text-sm">Yes</button>
            </div>
        </div>
    </Modal>

    <!-- Finish Order Confirmation -->
    <Modal :show="showFinishOrderModal" @close="closeModals" max-width="md">
        <div class="p-6 bg-zinc-800 text-white">
            <b>Confirmation</b>
            <p class="mt-2">Are you sure this order is already done?</p>
            <p class="text-sm">This action cannot be undone.</p>
            <div class="flex justify-end space-x-4">
                <button @click="closeModals" class="hover:underline">No</button>
                <button @click="finishOrder" class="bg-red-500 hover:bg-red-700 active:bg-red-900 duration-200 ease-in-out px-2 py-1 rounded-lg text-sm">Yes</button>
            </div>
        </div>
    </Modal>
</template>

<style scoped>
.list-move,
.list-enter-active,
.list-leave-active {
    transition: all 0.5s ease;
}

.list-enter-from {
    opacity: 0;
    transform: translateX(100%);
}

.list-leave-to {
    opacity: 0;
    transform: translateX(-30px);
}

.list-leave-active {
    position: absolute;
}
</style>