import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'
import LoginView from '../views/LoginView.vue'
import LocationView from '../views/LocationView.vue'
import MapView from '../views/MapView.vue'
import TripView from '../views/TripView.vue'
import StandByView from '../views/StandByView.vue'
import DriverView from '../views/DriverView.vue'
import DrivingView from '../views/DrivingView.vue'
import { api_url } from '../helpers/http'
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
    },
    {
      path: '/trip',
      name: 'trip',
      component: TripView
    },
    {
      path: '/standby',
      name: 'standby',
      component: StandByView
    },
    {
      path: '/driver',
      name: 'driver',
      component: DriverView
    }
    ,
    {
      path: '/driving',
      name: 'driving',
      component: DrivingView
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
  axios.get(api_url+'/api/user', {
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
