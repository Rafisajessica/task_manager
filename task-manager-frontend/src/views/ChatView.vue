<template>
    <div class="flex flex-col h-full">
    <!-- Titre -->
    <div class="flex items-center gap-3 mb-6">
      <div class="w-10 h-10 bg-purple-600/20 rounded-xl flex items-center justify-center">
        <CpuChipIcon class="w-5 h-5 text-purple-400" />
      </div>
      <div>
        <h1 class="text-2xl font-bold text-white">Assistant IA</h1>
      </div>
    </div>

    <!-- Messages -->
    <div ref="messagesContainer"
      class="flex-1 bg-gray-800 rounded-xl border border-gray-700 p-6 overflow-y-auto mb-4 space-y-4"
      style="max-height: calc(100vh - 280px)">

      <!-- Message de bienvenue -->
      <div v-if="chatStore.messages.length === 0" class="text-center py-12">
        <p class="text-4xl mb-3"></p>
        <p class="text-white font-medium">Assistant IA Task Manager</p>
        <p class="text-gray-400 text-sm mt-2">Posez-moi une question sur vos tâches !</p>
        <div class="flex flex-wrap gap-2 justify-center mt-4">
          <button v-for="suggestion in suggestions" :key="suggestion"
            @click="sendSuggestion(suggestion)"
            class="bg-gray-700 hover:bg-gray-600 text-gray-300 text-xs px-3 py-2 rounded-lg transition">
            {{ suggestion }}
          </button>
        </div>
      </div>

      <!-- Messages -->
      <div v-for="(msg, i) in chatStore.messages" :key="i"
        :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'">
        <div :class="[
          'max-w-lg px-4 py-3 rounded-2xl text-sm',
          msg.role === 'user'
            ? 'bg-blue-600 text-white rounded-br-sm'
            : 'bg-gray-700 text-gray-100 rounded-bl-sm'
        ]">
          <p class="whitespace-pre-wrap">{{ msg.content }}</p>
        </div>
      </div>

      <!-- Typing indicator -->
      <div v-if="chatStore.loading" class="flex justify-start">
        <div class="bg-gray-700 px-4 py-3 rounded-2xl rounded-bl-sm">
          <div class="flex gap-1">
            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:0ms"></span>
            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:150ms"></span>
            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:300ms"></span>
          </div>
        </div>
      </div>
    </div>

    <!-- Input -->
    <div class="flex gap-3">
      <input
        v-model="message"
        @keyup.enter="handleSend"
        type="text"
        placeholder="Posez une question à votre assistant IA..."
        class="flex-1 bg-gray-800 text-white rounded-xl px-4 py-3 border border-gray-700
               focus:border-blue-500 outline-none transition"
      />
      <button @click="handleSend" :disabled="chatStore.loading || !message.trim()"
        class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white px-6 py-3
               rounded-xl font-medium transition">
        Envoyer
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick, watch } from 'vue'
import { useChatStore } from '../stores/chat'
import { CpuChipIcon } from '@heroicons/vue/24/outline'
const chatStore         = useChatStore()
const message           = ref('')
const messagesContainer = ref(null)

const suggestions = [
  'Que dois-je faire aujourd\'hui ?',
  'Quelles tâches sont urgentes ?',
  'Donne-moi un résumé de mes tâches',
]

onMounted(() => chatStore.fetchHistory())

watch(() => chatStore.messages.length, async () => {
  await nextTick()
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
})

async function handleSend() {
  if (!message.value.trim() || chatStore.loading) return
  const msg = message.value
  message.value = ''
  await chatStore.sendMessage(msg)
}

function sendSuggestion(text) {
  message.value = text
  handleSend()
}
</script>