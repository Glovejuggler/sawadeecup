<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import Modal from '@/Components/Modal.vue';
import { domToPng } from 'modern-screenshot';

defineOptions({
    layout: null
})

const props = defineProps({
    items: Object,
    categories: Object
})

const visibleItems = ref(null)
const currentTab = ref(null)
const isProcessing = ref(false)
const isAndroid = ref(false)

const openCart = ref(false)

const changeCat = (cat) => {
    currentTab.value = cat
    visibleItems.value = props.items[cat]
}

const cart = ref(null)
const cartValue = computed(() => {
    return cart.value?.reduce((acc, item) => acc + item.price * item.count, 0)
})
const payment = ref(0)
const customer = ref('')
const type = ref('')
const note = ref('')

const receipt = ref(null)
const askReceipt = ref(false)
const noteModal = ref(false)
const showConfirmation = ref(false)
const confirmationMessage = ref(null)
const showMessage = () => {
    showConfirmation.value = true
}

const addToCart = (item) => {
    if (cart.value) {
        var dupe = cart.value.findIndex((i) => i.id === item.id)
        if (dupe == -1) {
            item.count = 1
            cart.value.push(item)
        } else {
            cart.value[dupe].count += 1
        }
    } else {
        item.count = 1
        cart.value = [item]
    }
}

const decreaseCount = (item) => {
    var cartItem = cart.value.findIndex((i) => i.id === item.id)
    cart.value[cartItem].count -= 1
    if (cart.value[cartItem].count === 0) {
        cart.value.splice(cartItem, 1)
    }
    if (!cart.value.length) payment.value = 0
}

const removeItem = (item) => {
    var cartItem = cart.value.findIndex((i) => i.id === item.id)
    cart.value.splice(cartItem, 1)
    if (!cart.value.length) payment.value = 0
}

const saveTransaction = (print) => {
    isProcessing.value = true
    askReceipt.value = false
    axios.post(route('transaction.save'), {
        items: cart.value,
        payment: payment.value,
        name: customer.value,
        type: type.value,
        note: note.value
    }, {
        maxBodyLength: 2048,
        maxContentLength: 2048,
    }).then((d) => {
        confirmationMessage.value = d.data.message
        receipt.value = d.data.transaction
    }).catch((err) => {
        console.log(err)
    }).finally(() => {
        cart.value = null
        payment.value = null
        customer.value = null
        type.value = null
        note.value = null
        isProcessing.value = false
        if (print) {
            printReceipt()
        }
        showMessage()
    })
}

onMounted(() => {
    changeCat(props.categories[0].name)
    let ua = navigator.userAgent.toLowerCase()
    isAndroid.value = ua.indexOf('android') > -1
})

const printReceipt = () => {
    const r = document.getElementById('receipt')
    domToPng(r, {
        quality: 1,
        scale: 2,
    }).then((e) => {
        console.log(e)
        if (isAndroid.value) {
            window.location.href = "rawbt:" + e;
        }
    })
}

const notif = new Audio('../notif.mp3');
Echo.private('done-orders')
    .listen('OrderDone', (e) => {
        notif.play()
    });
</script>

<template>
    <Head>
        <title>
            POS
        </title>
    </Head>

    <main class="fixed inset-0 overflow-hidden min-h-screen max-h-screen bg-zinc-900 sm:hidden md:block">
        <div class="grid grid-cols-12">
            <aside class="bg-zinc-900 flex flex-col space-y-2 col-span-2 p-2 text-xs md:text-sm min-h-screen max-h-screen z-50 overflow-y-auto">
                    <div v-for="category in categories"
                        :class="{ 'bg-zinc-600 font-bold px-5': currentTab === category.name }"
                        @click="changeCat(category.name)" class="py-2 px-4 text-white hover:bg-zinc-700 rounded-lg duration-200 ease-in-out">{{
                category.name }}</div>
            </aside>
        
            <div class="select-none overflow-y-auto col-span-7 max-h-screen min-h-screen z-50" @contextmenu.prevent="">
                <div class="grid grid-cols-4 gap-2 p-2">
                    <div :style="`background: ${item.color ?? '#121212'} !important`" class="item flex w-full aspect-square relative active:scale-95 duration-150 ease-in-out rounded-md overflow-hidden"
                        v-for="item in visibleItems" @click="addToCart(item)" ontouchstart>
                        <img v-if="item.pic" draggable="false" @contextmenu.prevent="" @dragstart.prevent="" :src="`../storage/${item.pic}`"
                            class="w-full aspect-square object-cover" height="25px" width="25px">
                        <p class="absolute bottom-0 inset-x-0 bg-black/50 p-2 text-white text-xs md:text-sm lg:text-base">{{ item.name }}</p>
                        <span class="absolute top-2 right-2 bg-black/50 text-white rounded-lg p-1">{{ item.price }}</span>
                    </div>
                </div>
        
            </div>
        
            <div class="bg-zinc-900 p-4 col-span-3 min-h-screen max-h-screen">
                <div class="h-1/2 relative">
                    <div class="h-5/6 overflow-y-auto px-2 text-white">
                        <div class="rounded-lg flex justify-between bg-zinc-700 p-2 mb-2" v-for="item in cart">
                            <div class="flex space-x-2 items-center">
                                <i @click="decreaseCount(item)" class="bx bx-minus inline-flex justify-center items-center min-w-6 min-h-6 rounded-full bg-blue-500 hover:bg-blue-600 acive:bg-blue-800 duration-200 ease-in-out cursor-pointer"></i>
                                <p>{{ item.name }} x{{ item.count }}</p>
                            </div>

                            <div class="flex space-x-2 items-center">
                                <p>{{ item.price * item.count }}</p>
                                <i @click="removeItem(item)" class="bx bx-trash inline-flex justify-center items-center min-w-6 min-h-6 rounded-full bg-red-500 hover:bg-red-600 acive:bg-red-800 duration-200 ease-in-out cursor-pointer"></i>
                            </div>
                        </div>
                    </div>
                    
                    <div v-if="cartValue > 0" class="absolute bottom-2 text-white w-full">
                        <div class="flex justify-between">
                            <p class="font-bold">Total: <span
                                    class="text-xl font-black">{{ cartValue }}</span></p>
                            <p v-if="payment > cartValue" class="font-bold">Change: <span
                                class="text-xl font-black">{{ cartValue && payment - cartValue }}</span></p>
                        </div>
                    </div>
                </div>
        
                <div class="h-1/2 relative">
                    <label class="relative block mb-1">
                        <input :disabled="!cartValue" v-model="customer"
                            class="[appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none duration-300 ease-in-out placeholder:text-xs placeholder:text-zinc-400 text-white block bg-zinc-900 w-full border-slate-300 dark:border-slate-300/20 rounded-md py-2 pl-3 pr-9 shadow-sm focus:border-indigo-300 focus:ring-indigo-200 focus:ring focus:ring-opacity-50"
                            placeholder="Customer" type="text" name="customer" />
                        <i @click.self="customer = null"
                            class='bx bx-x-circle text-white/40 absolute text-xl inset-y-0 right-0 flex items-center pr-3 hover:text-red-500'></i>
                    </label>
                    <label class="relative block">
                        <input :disabled="!cartValue" v-model="payment"
                            class="[appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none duration-300 ease-in-out placeholder:text-xs placeholder:text-zinc-400 text-white block bg-zinc-900 w-full border-slate-300 dark:border-slate-300/20 rounded-md py-2 pl-3 pr-9 shadow-sm focus:border-indigo-300 focus:ring-indigo-200 focus:ring focus:ring-opacity-50"
                            placeholder="Payment" type="number" name="payment" />
                        <i @click.self="payment = null"
                            class='bx bx-x-circle text-white/40 absolute text-xl inset-y-0 right-0 flex items-center pr-3 hover:text-red-500'></i>
                    </label>
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-2 my-2">
                        <button :disabled="!cartValue" class="p-2 text-sm font-bold text-zinc-800 bg-white rounded-md enabled:active:scale-95 disabled:opacity-50"
                            @click="payment += 50">50</button>
                        <button :disabled="!cartValue" class="p-2 text-sm font-bold text-zinc-800 bg-white rounded-md enabled:active:scale-95 disabled:opacity-50"
                            @click="payment += 100">100</button>
                        <button :disabled="!cartValue" class="p-2 text-sm font-bold text-zinc-800 bg-white rounded-md enabled:active:scale-95 disabled:opacity-50"
                            @click="payment += 200">200</button>
                        <button :disabled="!cartValue" class="p-2 text-sm font-bold text-zinc-800 bg-white rounded-md enabled:active:scale-95 disabled:opacity-50"
                            @click="payment += 500">500</button>
                        <button :disabled="!cartValue" class="p-2 text-xs font-bold text-zinc-800 bg-white rounded-md enabled:active:scale-95 disabled:opacity-50"
                            @click="payment += 1000">1000</button>
                        <button :disabled="!cartValue" class="bg-white rounded-md w-full p-2 font-bold text-sm enabled:active:scale-[0.98] disabled:opacity-50 col-span-5"
                            @click="payment = cartValue">Exact amount</button>
                    </div>
        
                    <input :disabled="!cartValue" type="radio" name="type" id="dinein" value="Dine-in" class="hidden" v-model="type">
                    <input :disabled="!cartValue" type="radio" name="type" id="takeout" value="Take-out" class="hidden" v-model="type">
                    <div class="flex justify-between text-white space-x-2">
                            <label class="text-sm rounded-2xl py-1 w-full inline-flex justify-center duration-200 ease-in-out" :class="type === 'Dine-in' ? 'bg-white font-bold text-zinc-800' : 'bg-zinc-600 text-zinc-200', {'opacity-50': !cartValue}" for="dinein">Dine-in</label>
                            <label class="text-sm rounded-2xl py-1 w-full inline-flex justify-center duration-200 ease-in-out" :class="type === 'Take-out' ? 'bg-white font-bold text-zinc-800' : 'bg-zinc-600 text-zinc-200', {'opacity-50': !cartValue}" for="takeout">Take-out</label>
                    </div>
        
                    <button
                        class="bg-white disabled:opacity-50 rounded-md text-sm w-full p-2 font-bold enabled:active:scale-[0.98] absolute bottom-0 right-0"
                        @click="noteModal = true"
                        :disabled="!type || !cartValue || payment < cartValue || !payment || isProcessing">{{ isProcessing ? 'Processing...' : 'Place order' }}</button>
                </div>
            </div>
        </div>

        <!-- Receipt -->
        <div v-if="receipt" id="receipt" class="w-64 bg-white pb-6 z-10">
            <div class="flex justify-center">
                <img src="logo.jpg" class="w-32" alt="">
            </div>
            <div class="grid grid-cols-2">
                <p>{{ receipt.name ?? ' ' }}</p>
                <p class="text-right">{{ `Order #${receipt.number}` }}</p>
            </div>
            <div class="grid grid-cols-2">
                <b>Type</b>
                <b class="text-right">{{ receipt.type }}</b>
            </div>
            <hr class="border-black mt-2">
            <div class="flex justify-between">
                <p>Item</p>
                <p>Subtotal</p>
            </div>
            <hr class="border-black mb-2">
            <div>
                <div v-for="item in receipt.items">
                    <p>{{ item.name }}</p>
                    <div class="flex justify-between pl-4 w-full">
                        <p class="w-full">{{ Number(item.price).amountFormat() }} x{{ item.quantity }}</p>
                        <p>{{ (item.quantity * item.price).amountFormat() }}</p>
                    </div>
                </div>
                <hr class="border-black my-2">
                <div>
                    <div class="font-bold text-lg flex justify-between">
                        <p>TOTAL</p>
                        <p>{{ receipt.gross.amountFormat() }}</p>
                    </div>
                    <div class="pl-4">
                        <p>CASH: {{ receipt.payment.amountFormat() }}</p>
                        <p>CHANGE: {{ (receipt.payment - receipt.gross).amountFormat() }}</p>
                    </div>
                </div>
                <hr class="border-black my-2">
                <div class="flex justify-center">
                    <p>Thank you. Come again.</p>
                </div>
                <div class="flex justify-between text-sm">
                    <p>Cashier</p>
                    <p>{{ $page.props.auth.user.name }}</p>
                </div>
                <div class="flex justify-between text-sm">
                    <p>Order ID</p>
                    <p class="text-right">{{ `dQw4w9WgXcQ${receipt.id.pad(4)}` }}</p>
                </div>
                <div class="flex justify-between text-sm">
                    <p class="w-max">Order Time</p>
                    <p class="w-max">{{ Intl.DateTimeFormat('en-US', {month: '2-digit', day: '2-digit', year: 'numeric', hour: '2-digit', minute: 'numeric', hour12: true}).format(new Date(receipt.created_at)) }}</p>
                </div>
                    <div class="flex flex-col items-center justify-center text-sm mt-2 w-full">
                        <b>UPTop Diner</b>
                        <p>25 L. Geirosa Ave., Brgy. Silangan</p>
                        <p>Calauan, Laguna</p>
                        <p>La Famiglia Building 2nd Floor</p>
                    </div>
                <hr class="border-black my-2">
                <p>THIS IS NOT AN OFFICIAL RECEIPT</p>
            </div>
        </div>
    </main>

    <main class="md:hidden sm:block fixed inset-0 bg-zinc-900 min-h-screen">
        <nav class="p-2 flex space-x-2 overflow-x-scroll">
            <span @click="changeCat(category.name)" class="inline-flex font-semibold px-2 rounded-full border-white border whitespace-nowrap text-clip h-min" :class="currentTab === category.name ? 'text-zinc-900 bg-white' : 'text-white bg-zinc-900'" v-for="category in categories">
                {{ category.name }}
            </span>
        </nav>

        <div class="grid grid-cols-2 gap-2 p-2">
            <div :style="`background: ${item.color ?? '#121212'} !important`" class="item flex w-full aspect-square relative active:scale-95 duration-150 ease-in-out rounded-md overflow-hidden"
                v-for="item in visibleItems" @click="addToCart(item)" ontouchstart>
                <img v-if="item.pic" draggable="false" @contextmenu.prevent="" @dragstart.prevent="" :src="`../storage/${item.pic}`"
                    class="w-full aspect-square object-cover" height="25px" width="25px">
                <p class="absolute bottom-0 inset-x-0 bg-black/50 p-2 text-white text-xs md:text-sm lg:text-base">{{ item.name }}</p>
                <span class="absolute top-2 right-2 bg-black/50 text-white rounded-lg p-1">{{ item.price }}</span>
            </div>
        </div>

        <div v-if="!openCart" class="fixed z-40 w-full bottom-0 inset-x-0" @click="openCart = true">
            <div class="flex justify-center">
                <i class='bx bx-caret-up text-zinc-900 bg-white px-10 rounded-t-3xl'></i>
            </div>
        </div>

        <!-- Cart -->
        <Transition enter-from-class="translate-y-full" enter-active-class="ease-in-out duration-100" leave-active-class="ease-in-out duration-100"leave-to-class="translate-y-full">
            <div class="fixed inset-0 bg-zinc-900" v-if="openCart">
                <div class="fixed z-40 w-full top-0 inset-x-0" @click="openCart = false">
                    <div class="flex justify-center">
                        <i class='bx bx-caret-down text-zinc-900 bg-white px-10 rounded-b-3xl'></i>
                    </div>
                </div>

                <div class="pt-8 px-2 bg-zinc-900 text-white z-50">
                    <p class="font-semibold mb-2">Cart</p>
                    <div class="rounded-lg flex justify-between bg-zinc-700 p-2 mb-2 text-white" v-for="item in cart">
                        <div class="flex space-x-2 items-center">
                            <i @click="decreaseCount(item)" class="bx bx-minus inline-flex justify-center items-center min-w-6 min-h-6 rounded-full bg-blue-500 hover:bg-blue-600 acive:bg-blue-800 duration-200 ease-in-out cursor-pointer"></i>
                            <p>{{ item.name }} x{{ item.count }}</p>
                        </div>
    
                        <div class="flex space-x-2 items-center">
                            <p>{{ item.price * item.count }}</p>
                            <i @click="removeItem(item)" class="bx bx-trash inline-flex justify-center items-center min-w-6 min-h-6 rounded-full bg-red-500 hover:bg-red-600 acive:bg-red-800 duration-200 ease-in-out cursor-pointer"></i>
                        </div>
                    </div>
                </div>
    
                <div class="fixed inset-x-0 bottom-0 p-2 z-50">
                    <label class="relative block mb-1">
                        <input :disabled="!cartValue" v-model="customer"
                            class="[appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none duration-300 ease-in-out placeholder:text-xs placeholder:text-zinc-400 text-white block bg-zinc-900 w-full border-slate-300 dark:border-slate-300/20 rounded-md py-2 pl-3 pr-9 shadow-sm focus:border-indigo-300 focus:ring-indigo-200 focus:ring focus:ring-opacity-50"
                            placeholder="Customer" type="text" name="customer" />
                        <i @click.self="customer = null"
                            class='bx bx-x-circle text-white/40 absolute text-xl inset-y-0 right-0 flex items-center pr-3 hover:text-red-500'></i>
                    </label>
                    <label class="relative block">
                        <input :disabled="!cartValue" v-model="payment"
                            class="[appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none duration-300 ease-in-out placeholder:text-xs placeholder:text-zinc-400 text-white block bg-zinc-900 w-full border-slate-300 dark:border-slate-300/20 rounded-md py-2 pl-3 pr-9 shadow-sm focus:border-indigo-300 focus:ring-indigo-200 focus:ring focus:ring-opacity-50"
                            placeholder="Payment" type="number" name="payment" />
                        <i @click.self="payment = null"
                            class='bx bx-x-circle text-white/40 absolute text-xl inset-y-0 right-0 flex items-center pr-3 hover:text-red-500'></i>
                    </label>
                    <div class="grid grid-cols-5 gap-2 my-2">
                        <button :disabled="!cartValue" class="p-2 text-sm font-bold text-zinc-800 bg-white rounded-md enabled:active:scale-95 disabled:opacity-50"
                            @click="payment += 50">50</button>
                        <button :disabled="!cartValue" class="p-2 text-sm font-bold text-zinc-800 bg-white rounded-md enabled:active:scale-95 disabled:opacity-50"
                            @click="payment += 100">100</button>
                        <button :disabled="!cartValue" class="p-2 text-sm font-bold text-zinc-800 bg-white rounded-md enabled:active:scale-95 disabled:opacity-50"
                            @click="payment += 200">200</button>
                        <button :disabled="!cartValue" class="p-2 text-sm font-bold text-zinc-800 bg-white rounded-md enabled:active:scale-95 disabled:opacity-50"
                            @click="payment += 500">500</button>
                        <button :disabled="!cartValue" class="p-2 text-xs font-bold text-zinc-800 bg-white rounded-md enabled:active:scale-95 disabled:opacity-50"
                            @click="payment += 1000">1000</button>
                        <button :disabled="!cartValue" class="bg-white rounded-md w-full p-2 font-bold text-sm enabled:active:scale-[0.98] disabled:opacity-50 col-span-5"
                            @click="payment = cartValue">Exact amount</button>
                    </div>
        
                    <input :disabled="!cartValue" type="radio" name="type" id="dinein" value="Dine-in" class="hidden" v-model="type">
                    <input :disabled="!cartValue" type="radio" name="type" id="takeout" value="Take-out" class="hidden" v-model="type">
                    <div class="flex justify-between text-white space-x-2 my-2">
                            <label class="text-sm rounded-2xl py-1 w-full inline-flex justify-center duration-200 ease-in-out" :class="type === 'Dine-in' ? 'bg-white font-bold text-zinc-800' : 'bg-zinc-600 text-zinc-200', {'opacity-50': !cartValue}" for="dinein">Dine-in</label>
                            <label class="text-sm rounded-2xl py-1 w-full inline-flex justify-center duration-200 ease-in-out" :class="type === 'Take-out' ? 'bg-white font-bold text-zinc-800' : 'bg-zinc-600 text-zinc-200', {'opacity-50': !cartValue}" for="takeout">Take-out</label>
                    </div>
        
                    <button
                        class="bg-white disabled:opacity-50 rounded-md text-sm w-full p-2 font-bold enabled:active:scale-[0.98]"
                        @click="noteModal = true"
                        :disabled="!type || !cartValue || payment < cartValue || !payment || isProcessing">{{ isProcessing ? 'Processing...' : 'Place order' }}</button>
                </div>
            </div>
        </Transition>
        <!-- Receipt -->
        <div v-if="receipt" id="receipt" class="w-64 bg-white pb-6 -z-50">
            <div class="flex justify-center">
                <img src="logo.jpg" class="w-32" alt="">
            </div>
            <div class="grid grid-cols-2">
                <p>{{ receipt.name ?? ' ' }}</p>
                <p class="text-right">{{ `Order #${receipt.number}` }}</p>
            </div>
            <div class="grid grid-cols-2">
                <b>Type</b>
                <b class="text-right">{{ receipt.type }}</b>
            </div>
            <hr class="border-black mt-2">
            <div class="flex justify-between">
                <p>Item</p>
                <p>Subtotal</p>
            </div>
            <hr class="border-black mb-2">
            <div>
                <div v-for="item in receipt.items">
                    <p>{{ item.name }}</p>
                    <div class="flex justify-between pl-4 w-full">
                        <p class="w-full">{{ Number(item.price).amountFormat() }} x{{ item.quantity }}</p>
                        <p>{{ (item.quantity * item.price).amountFormat() }}</p>
                    </div>
                </div>
                <hr class="border-black my-2">
                <div>
                    <div class="font-bold text-lg flex justify-between">
                        <p>TOTAL</p>
                        <p>{{ receipt.gross.amountFormat() }}</p>
                    </div>
                    <div class="pl-4">
                        <p>CASH: {{ receipt.payment.amountFormat() }}</p>
                        <p>CHANGE: {{ (receipt.payment - receipt.gross).amountFormat() }}</p>
                    </div>
                </div>
                <hr class="border-black my-2">
                <div class="flex justify-center">
                    <p>Thank you. Come again.</p>
                </div>
                <div class="flex justify-between text-sm">
                    <p>Cashier</p>
                    <p>{{ $page.props.auth.user.name }}</p>
                </div>
                <div class="flex justify-between text-sm">
                    <p>Order ID</p>
                    <p class="text-right">{{ `dQw4w9WgXcQ${receipt.id.pad(4)}` }}</p>
                </div>
                <div class="flex justify-between text-sm">
                    <p class="w-max">Order Time</p>
                    <p class="w-max">{{ Intl.DateTimeFormat('en-US', {month: '2-digit', day: '2-digit', year: 'numeric', hour: '2-digit', minute: 'numeric', hour12: true}).format(new Date(receipt.created_at)) }}</p>
                </div>
                    <div class="flex flex-col items-center justify-center text-sm mt-2 w-full">
                        <b>UPTop Diner</b>
                        <p>25 L. Geirosa Ave., Brgy. Silangan</p>
                        <p>Calauan, Laguna</p>
                        <p>La Famiglia Building 2nd Floor</p>
                    </div>
                <hr class="border-black my-2">
                <p>THIS IS NOT AN OFFICIAL RECEIPT</p>
            </div>
        </div>

    </main>

    <!-- Messages Modal -->
    <Modal :max-width="'sm'" :show="showConfirmation" @close="showConfirmation = false">
        <div class="flex justify-center p-4 bg-white  text-zinc-900 text-3xl font-bold uppercase">
            {{ confirmationMessage }}
        </div>
    </Modal>

    <!-- Note Modal -->
     <Modal :max-width="sm" :show="noteModal">
        <div class="p-6 bg-zinc-800 text-white">
            <label for="note">Note</label>
            <div class="flex w-full">
                <textarea name="note" id="note" rows="5" v-model="note"
                    class="[appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none w-full border-zinc-700 bg-zinc-900 text-zinc-300 focus:border-indigo-600 focus:ring-indigo-600 rounded-md shadow-sm"></textarea>
            </div>
            <div class="flex justify-end items-center space-x-4 mt-4">
                <span class="hover:underline cursor-pointer" @click="noteModal = false">Cancel</span>
                <button @click="() => {
                    askReceipt = true
                    noteModal = false
                }" class="px-8 py-2 bg-green-500 hover:bg-green-600 active:bg-green-800 rounded-lg ease-in-out duration-200">Next</button>
            </div>
        </div>
     </Modal>

    <!-- Ask to print receipt -->
    <Modal max-width="sm" :show="askReceipt" @close="askReceipt = false">
        <div class="p-4 bg-zinc-800 text-white">
            <span class="text-lg">Do you want to print receipt?</span>
            <div class="grid grid-cols-2 gap-2 mt-4">
                <button @click="saveTransaction(true)" class="w-full py-2 text-lg bg-blue-500 rounded-lg">Yes</button>
                <button @click="saveTransaction(false)" class="w-full py-2 text-lg bg-red-500 rounded-lg">No</button>
            </div>
        </div>
    </Modal>
</template>

<style scoped>
/* width */
::-webkit-scrollbar {
  width: 5px;
}

/* Track */
::-webkit-scrollbar-track {
  background: #18181b;
}

/* Handle */
::-webkit-scrollbar-thumb {
  background: #555;
}

/* Handle on hover */
::-webkit-scrollbar-thumb:hover {
  background: #777;
}
</style>