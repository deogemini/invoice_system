import { createRouter, createWebHistory } from 'vue-router';
import { auth, fetchCurrentUser, isAuthenticated, isAdministrator } from './auth';
import Login from './components/auth/Login.vue';
import ForgotPassword from './components/auth/ForgotPassword.vue';
import AccessDenied from './components/auth/AccessDenied.vue';
import AdminDashboard from './components/dashboards/AdminDashboard.vue';
import ClientDashboard from './components/dashboards/ClientDashboard.vue';
import ClientList from './components/admin/ClientList.vue';
import ClientForm from './components/admin/ClientForm.vue';
import CustomerList from './components/customers/CustomerList.vue';
import CustomerForm from './components/customers/CustomerForm.vue';
import ProductList from './components/products/ProductList.vue';
import ProductForm from './components/products/ProductForm.vue';
import InvoiceList from './components/invoices/InvoiceList.vue';
import InvoiceForm from './components/invoices/InvoiceForm.vue';
import InvoicePrint from './components/invoices/InvoicePrint.vue';
import BankAccountList from './components/bankaccounts/BankAccountList.vue';
import BankAccountForm from './components/bankaccounts/BankAccountForm.vue';
import CompanySettingForm from './components/settings/CompanySettingForm.vue';

const routes = [
    {
        path: '/login',
        name: 'login',
        component: Login,
        meta: { guest: true },
    },
    {
        path: '/forgot-password',
        name: 'forgot-password',
        component: ForgotPassword,
        meta: { guest: true },
    },
    {
        path: '/access-denied',
        name: 'access-denied',
        component: AccessDenied,
        meta: { requiresAuth: true },
    },
    {
        path: '/',
        redirect: () => ({ name: isAdministrator() ? 'admin.dashboard' : 'client.dashboard' }),
        meta: { requiresAuth: true },
    },
    {
        path: '/admin/dashboard',
        name: 'admin.dashboard',
        component: AdminDashboard,
        meta: { requiresAuth: true, role: 'administrator' },
    },
    {
        path: '/client/dashboard',
        name: 'client.dashboard',
        component: ClientDashboard,
        meta: { requiresAuth: true, role: 'client' },
    },
    {
        path: '/admin/clients',
        name: 'admin.clients.index',
        component: ClientList,
        meta: { requiresAuth: true, role: 'administrator' },
    },
    {
        path: '/admin/clients/create',
        name: 'admin.clients.create',
        component: ClientForm,
        meta: { requiresAuth: true, role: 'administrator' },
    },
    {
        path: '/admin/clients/:id/edit',
        name: 'admin.clients.edit',
        component: ClientForm,
        props: true,
        meta: { requiresAuth: true, role: 'administrator' },
    },
    {
        path: '/settings',
        name: 'settings.edit',
        component: CompanySettingForm,
        meta: { requiresAuth: true },
    },
    {
        path: '/customers',
        name: 'customers.index',
        component: CustomerList,
        meta: { requiresAuth: true },
    },
    {
        path: '/customers/create',
        name: 'customers.create',
        component: CustomerForm,
        meta: { requiresAuth: true },
    },
    {
        path: '/customers/:id/edit',
        name: 'customers.edit',
        component: CustomerForm,
        props: true,
        meta: { requiresAuth: true },
    },
    {
        path: '/products',
        name: 'products.index',
        component: ProductList,
        meta: { requiresAuth: true },
    },
    {
        path: '/products/create',
        name: 'products.create',
        component: ProductForm,
        meta: { requiresAuth: true },
    },
    {
        path: '/products/:id/edit',
        name: 'products.edit',
        component: ProductForm,
        props: true,
        meta: { requiresAuth: true },
    },
    {
        path: '/invoices',
        name: 'invoices.index',
        component: InvoiceList,
        meta: { requiresAuth: true },
    },
    {
        path: '/invoices/create',
        name: 'invoices.create',
        component: InvoiceForm,
        meta: { requiresAuth: true },
    },
    {
        path: '/invoices/:id/edit',
        name: 'invoices.edit',
        component: InvoiceForm,
        props: true,
        meta: { requiresAuth: true },
    },
    {
        path: '/invoices/:id/print',
        name: 'invoices.print',
        component: InvoicePrint,
        props: true,
        meta: { requiresAuth: true },
    },
    {
        path: '/bankaccounts',
        name: 'bankaccounts.index',
        component: BankAccountList,
        meta: { requiresAuth: true },
    },
    {
        path: '/bankaccounts/create',
        name: 'bankaccounts.create',
        component: BankAccountForm,
        meta: { requiresAuth: true },
    },
    {
        path: '/bankaccounts/:id/edit',
        name: 'bankaccounts.edit',
        component: BankAccountForm,
        props: true,
        meta: { requiresAuth: true },
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach(async (to) => {
    if (auth.loading) {
        await fetchCurrentUser();
    }

    if (to.meta.guest && isAuthenticated()) {
        return { name: isAdministrator() ? 'admin.dashboard' : 'client.dashboard' };
    }

    if (to.meta.requiresAuth && !isAuthenticated()) {
        return { name: 'login', query: { redirect: to.fullPath } };
    }

    if (to.meta.role && auth.user?.role !== to.meta.role) {
        return { name: 'access-denied' };
    }
});

export default router;
