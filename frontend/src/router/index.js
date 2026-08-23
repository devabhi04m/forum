import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../modules/auth/stores/auth';

import ForumHome from '../modules/forum/pages/ForumHome.vue';
import CategoryPage from '../modules/forum/pages/CategoryPage.vue';
import ThreadPage from '../modules/forum/pages/ThreadPage.vue';
import CreateThreadPage from '../modules/forum/pages/CreateThreadPage.vue';
import EditThreadPage from '../modules/forum/pages/EditThreadPage.vue';
import SearchPage from '../modules/forum/pages/SearchPage.vue';
import ProfilePage from '../modules/forum/pages/ProfilePage.vue';
import ModerationPage from '../modules/forum/pages/ModerationPage.vue';
import LoginPage from '../modules/auth/pages/LoginPage.vue';
import RegisterPage from '../modules/auth/pages/RegisterPage.vue';
import AdminLayout from '../modules/admin/AdminLayout.vue';
import AdminDashboard from '../modules/admin/pages/AdminDashboard.vue';
import AdminUsers from '../modules/admin/pages/AdminUsers.vue';
import AdminThreads from '../modules/admin/pages/AdminThreads.vue';
import AdminPosts from '../modules/admin/pages/AdminPosts.vue';
import AdminCategories from '../modules/admin/pages/AdminCategories.vue';
import AdminTags from '../modules/admin/pages/AdminTags.vue';
import AdminReports from '../modules/admin/pages/AdminReports.vue';
import AdminDummyData from '../modules/admin/pages/AdminDummyData.vue';
import AdminRoles from '../modules/admin/pages/AdminRoles.vue';
import AdminPermissions from '../modules/admin/pages/AdminPermissions.vue';

const routes = [
    { path: '/', name: 'forum.home', component: ForumHome },
    { path: '/categories/:slug', name: 'categories.show', component: CategoryPage },
    { path: '/search', name: 'search', component: SearchPage },
    { path: '/me', name: 'profile', component: ProfilePage, meta: { requiresAuth: true } },
    { path: '/moderation', name: 'moderation', component: ModerationPage, meta: { requiresAuth: true } },
    {
        path: '/admin',
        component: AdminLayout,
        meta: { requiresAuth: true, requiresAdmin: true },
        children: [
            { path: '', name: 'admin.dashboard', component: AdminDashboard },
            { path: 'users', name: 'admin.users', component: AdminUsers },
            { path: 'threads', name: 'admin.threads', component: AdminThreads },
            { path: 'posts', name: 'admin.posts', component: AdminPosts },
            { path: 'categories', name: 'admin.categories', component: AdminCategories },
            { path: 'tags', name: 'admin.tags', component: AdminTags },
            { path: 'reports', name: 'admin.reports', component: AdminReports },
            { path: 'roles', name: 'admin.roles', component: AdminRoles },
            { path: 'permissions', name: 'admin.permissions', component: AdminPermissions },
            { path: 'dummy-data', name: 'admin.dummy', component: AdminDummyData },
        ],
    },
    { path: '/threads/new', name: 'threads.create', component: CreateThreadPage, meta: { requiresAuth: true } },
    { path: '/threads/:slug', name: 'threads.show', component: ThreadPage },
    { path: '/threads/:slug/edit', name: 'threads.edit', component: EditThreadPage, meta: { requiresAuth: true } },
    { path: '/login', name: 'auth.login', component: LoginPage },
    { path: '/register', name: 'auth.register', component: RegisterPage },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior() {
        return { top: 0 };
    },
});

router.beforeEach((to) => {
    const auth = useAuthStore();
    if (to.meta.requiresAuth && !auth.isAuthenticated) {
        return { name: 'auth.login', query: { redirect: to.fullPath } };
    }
    if (to.meta.requiresAdmin && !auth.isAdmin) {
        return { name: 'forum.home' };
    }
    return true;
});

export default router;
