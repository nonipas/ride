import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import LoginView from '../views/LoginView.vue'
import LocationView from '../views/LocationView.vue'
import MapView from '../views/MapView.vue'
import axios from 'axios'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'Login',
      component: LoginView
    },
    {
      path: '/landing',
      name: 'landing',
      component: HomeView
    },
    {
      path: '/location',
      name: 'location',
      component: LocationView
    },
    {
      path: '/map',
      name: 'map',
      component: MapView
    }
  ]
})

router.beforeEach((to, from) => {
  if (to.name === 'Login'){
    return true
  }
  if (!localStorage.getItem('token')){
    return { name: 'Login'}
  }

    checkTokenAuthenticity()

})

const checkTokenAuthenticity = () => {
  axios.get('http://localhost/ride/back/api/user', {
    headers: {
      'Authorization': `Bearer ${localStorage.getItem('token')}`
    }
  })
  .then((response) => {})
  .catch((error) => {
    localStorage.removeItem('token')
    router.push({ name: 'Login' })
  })
}

export default router
