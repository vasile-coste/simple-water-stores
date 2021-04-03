import Vue from "vue";
import VueRouter from "vue-router";
import Home from "../views/Home.vue";

Vue.use(VueRouter);

const routes = [{
    path: "/",
    name: "Home",
    component: Home
},
{
    path: "/store/:id",
    name: "Store",
    component: () =>
        import("../views/Store.vue")
},
{
    path: "*",
    name: "Page404",
    component: () =>
        import("@/views/Page404.vue")
}
];

const router = new VueRouter({
    mode: "history",
    base: process.env.BASE_URL,
    routes
});

router.beforeEach((to, from, next) => {
    let title = to.name || 'GTS';
    document.title = `${title} | GTS`;
    next();
});

export default router;