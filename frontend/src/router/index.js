import { createRouter, createWebHistory } from 'vue-router'

import LoginView from '@/views/LoginView.vue'
import AspirantesView from '@/views/AspirantesView.vue'
import UsuariosView from '@/views/UsuariosView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),

  routes: [
    {
      path: '/',
      redirect: '/aspirantes',
    },

    {
      path: '/login',
      name: 'login',
      component: LoginView,
      meta: {
        public: true,
      },
    },

    {
      path: '/aspirantes',
      name: 'aspirantes',
      component: AspirantesView,
      meta: {
        requiresAuth: true,
      },
    },

    {
      path: '/usuarios',
      name: 'usuarios',
      component: UsuariosView,
      meta: {
        requiresAuth: true,
      },
    },
  ],
})

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('sivice_token')

  if (to.meta.requiresAuth && !token) {
    next('/login')
    return
  }

  if (to.path === '/login' && token) {
    next('/aspirantes')
    return
  }

  next()
})

export default router