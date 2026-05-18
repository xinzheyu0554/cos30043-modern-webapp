import { createRouter, createWebHistory } from "vue-router";
import LandingPage from "../pages/LandingPage.vue";
import HomePage from "../pages/HomePage.vue";
import ContentDetailPage from "../pages/ContentDetailPage.vue";
import AuthPage from "../pages/AuthPage.vue";
import ProfilePage from "../pages/ProfilePage.vue";
import FavouritesPage from "../pages/FavouritesPage.vue";
import ManageContentPage from "../pages/ManageContentPage.vue";
import AdminUsersPage from "../pages/AdminUsersPage.vue";
import AboutPage from "../pages/AboutPage.vue";
import SupportPage from "../pages/SupportPage.vue";
import { currentUser } from "../state/session";

const routes = [
  { path: "/", component: LandingPage, meta: { label: "Welcome" } },
  { path: "/browse", component: HomePage, meta: { label: "Browse Content" } },
  {
    path: "/content/:id",
    component: ContentDetailPage,
    meta: { label: "Content Detail" },
  },
  {
    path: "/login",
    component: AuthPage,
    meta: { guestOnly: true, label: "Login" },
  },
  {
    path: "/register",
    component: AuthPage,
    meta: { guestOnly: true, label: "Register" },
  },
  {
    path: "/profile",
    component: ProfilePage,
    meta: { requiresAuth: true, label: "Profile" },
  },
  {
    path: "/favourites",
    component: FavouritesPage,
    meta: { requiresAuth: true, label: "Saved Favourites" },
  },
  {
    path: "/manage-content",
    component: ManageContentPage,
    meta: {
      requiresAuth: true,
      roles: ["admin", "adminstaff"],
      label: "Manage Content",
    },
  },
  {
    path: "/admin/users",
    component: AdminUsersPage,
    meta: { requiresAuth: true, roles: ["admin"], label: "Admin Users" },
  },
  { path: "/about", component: AboutPage, meta: { label: "About MYWAY" } },
  { path: "/support", component: SupportPage, meta: { label: "Help And Support" } },
  { path: "/:pathMatch(.*)*", redirect: "/" },
];

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
  scrollBehavior() {
    return { top: 0 };
  },
});

router.beforeEach((to) => {
  const user = currentUser.value;

  if (to.meta.requiresAuth && !user) {
    return "/login";
  }

  if (to.meta.guestOnly && user) {
    return "/browse";
  }

  if (to.meta.roles?.length && !to.meta.roles.includes(user?.role)) {
    return "/browse";
  }

  return true;
});

export default router;
