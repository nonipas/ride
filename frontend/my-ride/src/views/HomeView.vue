

<template>
  <div class="flex flex-col items-center justify-center h-screen">
    <h1 class="text-4xl font-bold mb-8">Ride</h1>
    <div class="flex space-x-4">
      <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
        Start Driving
      </button>
      <button @click="handleFindRide" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
        Find A Ride
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()

const handleFindRide = () => {
  router.push({ name: 'location' })
}
</script>

