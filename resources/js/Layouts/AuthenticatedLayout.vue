<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div>
        <div class="min-h-screen bg-zinc-100 dark:bg-zinc-900">
            <nav class="bg-white dark:bg-zinc-800 border-b border-zinc-100 dark:border-zinc-700">
                <!-- Responsive Navigation Menu -->
                <div :class="{ block: showingNavigationDropdown, hidden: !showingNavigationDropdown }"
                    class="sm:hidden">
                    <div class="pt-2 pb-3 space-y-1">
                        <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')">
                            Dashboard
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div class="pt-4 pb-1 border-t border-zinc-200 dark:border-zinc-600">
                        <div class="px-4">
                            <div class="font-medium text-base text-zinc-800 dark:text-zinc-200">
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="font-medium text-sm text-zinc-500">{{ $page.props.auth.user.email }}</div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')"> Profile </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('logout')" method="post" as="button">
                                Log Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <aside class="md:w-48 bg-white dark:bg-zinc-900 dark:text-zinc-100 fixed left-0 inset-y-0 md:block hidden">
                <div class="flex flex-col space-y-1 py-8 mx-2 text-xs">
                    <Link :data-active="route().current('dashboard')" :href="route('dashboard')" class="px-4 py-1 rounded-lg data-[active=true]:bg-zinc-900 data-[active=true]:text-white dark:data-[active=true]:bg-white dark:data-[active=true]:text-zinc-900">
                        <i class="bx bxs-dashboard w-5 h-5 text-lg me-2"></i><span>Dashboard</span>
                    </Link>

                    <Link :data-active="route().current('items.index')" :href="route('items.index')" class="px-4 py-1 rounded-lg data-[active=true]:bg-zinc-900 data-[active=true]:text-white dark:data-[active=true]:bg-white dark:data-[active=true]:text-zinc-900">
                        <i class="bx bxs-dish w-5 h-5 text-lg me-2"></i><span>Products</span>
                    </Link>

                    <Link :data-active="route().current('sales')" :href="route('sales')" class="px-4 py-1 rounded-lg data-[active=true]:bg-zinc-900 data-[active=true]:text-white dark:data-[active=true]:bg-white dark:data-[active=true]:text-zinc-900">
                        <i class="bx bx-line-chart w-5 h-5 text-lg me-2"></i><span>Sales</span>
                    </Link>

                    <Link :data-active="route().current('unit.sales')" :href="route('unit.sales')" class="px-4 py-1 rounded-lg data-[active=true]:bg-zinc-900 data-[active=true]:text-white dark:data-[active=true]:bg-white dark:data-[active=true]:text-zinc-900">
                        <i class="bx bxs-bar-chart-alt-2 w-5 h-5 text-lg me-2"></i><span>Unit Sales</span>
                    </Link>

                    <Link :data-active="route().current('inventory.costing')" :href="route('inventory.costing')" class="px-4 py-1 rounded-lg data-[active=true]:bg-zinc-900 data-[active=true]:text-white dark:data-[active=true]:bg-white dark:data-[active=true]:text-zinc-900">
                        <i class="bx bx-scatter-chart w-5 h-5 text-lg me-2"></i><span>Inventory Costing</span>
                    </Link>

                    <Link v-if="$page.props.auth.user.id === 1" :data-active="route().current('users.index')" :href="route('users.index')" class="px-4 py-1 rounded-lg data-[active=true]:bg-zinc-900 data-[active=true]:text-white dark:data-[active=true]:bg-white dark:data-[active=true]:text-zinc-900">
                        <i class="bx bxs-group w-5 h-5 text-lg me-2"></i><span>User Management</span>
                    </Link>
                </div>

                <div class="fixed bottom-0 p-2 h-10 w-48">
                    <div class="flex justify-between items-center">
                        <p class="text-sm">{{ $page.props.auth.user.name }}</p>
                        <Link :href="route('logout')" method="post" as="button">
                            <i class="bx bx-log-out text-lg hover:text-red-500 duration-300 ease-in-out cursor-pointer"></i>
                        </Link>
                    </div>
                </div>
            </aside>

            <!-- Page Content -->
            <main class="md:ml-48">
                <slot />
            </main>
        </div>
    </div>
</template>
