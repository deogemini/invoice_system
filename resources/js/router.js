import { createRouter, createWebHistory } from 'vue-router';
import Home from './components/Home.vue';
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
        path: '/',
        name: 'home',
        component: Home,
    },
    {
        path: '/settings',
        name: 'settings.edit',
        component: CompanySettingForm,
    },
    {
        path: '/customers',
        name: 'customers.index',
        component: CustomerList,
    },
    {
        path: '/customers/create',
        name: 'customers.create',
        component: CustomerForm,
    },
    {
        path: '/customers/:id/edit',
        name: 'customers.edit',
        component: CustomerForm,
        props: true,
    },
    {
        path: '/products',
        name: 'products.index',
        component: ProductList,
    },
    {
        path: '/products/create',
        name: 'products.create',
        component: ProductForm,
    },
    {
        path: '/products/:id/edit',
        name: 'products.edit',
        component: ProductForm,
        props: true,
    },
    {
        path: '/invoices',
        name: 'invoices.index',
        component: InvoiceList,
    },
    {
        path: '/invoices/create',
        name: 'invoices.create',
        component: InvoiceForm,
    },
    {
        path: '/invoices/:id/print',
        name: 'invoices.print',
        component: InvoicePrint,
        props: true,
    },
    {
        path: '/bankaccounts',
        name: 'bankaccounts.index',
        component: BankAccountList,
    },
    {
        path: '/bankaccounts/create',
        name: 'bankaccounts.create',
        component: BankAccountForm,
    },
    {
        path: '/bankaccounts/:id/edit',
        name: 'bankaccounts.edit',
        component: BankAccountForm,
        props: true,
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
