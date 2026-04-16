<script setup>
import { ref, computed, nextTick, watch, onMounted } from 'vue'
import { useForm, Link, usePage } from '@inertiajs/vue3'
import {
  EnvelopeIcon,
  LockClosedIcon,
  EyeIcon,
  EyeSlashIcon,
  BuildingOffice2Icon,
  UserIcon,
  CheckCircleIcon,
  ShieldCheckIcon,
  IdentificationIcon,
  ScaleIcon
} from '@heroicons/vue/24/outline'
import AuthLayout from '@/Components/User/Layout/AuthLayout.vue'
import { useI18n } from 'vue-i18n'
import LanguageSwitcher from '@/Components/LanguageSwitcher.vue'

const { t } = useI18n()

const mode = ref('individual')
const showPassword = ref(false)

const form = useForm({
  email: '',
  password: '',
})

const submit = () => {
  form.post('/login')
}

const loginGoogle = () => {
  window.location.href = '/auth/google'
}

const leftContent = computed(() => {
  if (mode.value === 'company') {
    return {
      title: t('login.left.companyTitle'),
      description: t('login.left.companyDesc'),
      image: 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f',
      gradient: 'from-black/80 via-black/60 to-black/40'
    }
  } else {
    return {
      title: t('login.left.individualTitle'),
      description: t('login.left.individualDesc'),
      image: 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4',
      gradient: 'from-indigo-900/80 via-black/60 to-black/40'
    }
  }
})

const activeForm = ref(null)
const triggerRef = ref(null)
const showMobileInfo = ref(false)
const wrapperHeight = ref('auto')
const showDesktopInfo = ref(false)
let hoverTimeout = null
const popupStyle = ref({})

const updateHeight = () => {
  if (activeForm.value) {
    wrapperHeight.value = activeForm.value.offsetHeight + 'px'
  }
}

const handleMouseEnter = () => {
  if (window.innerWidth >= 768) {
    updatePopupPosition()
    showDesktopInfo.value = true
  }
}

const handleMouseLeave = () => {
  if (window.innerWidth >= 768) {
    hoverTimeout = setTimeout(() => {
      showDesktopInfo.value = false
    }, 100)
  }
}

const clearHoverTimeout = () => {
  if (hoverTimeout) clearTimeout(hoverTimeout)
}

const closeMobileInfo = () => {
  showMobileInfo.value = false
}

const toggleInfo = () => {
  if (window.innerWidth < 768) {
    showMobileInfo.value = !showMobileInfo.value
  } else {
    showDesktopInfo.value = !showDesktopInfo.value
    updatePopupPosition()
  }
}

const updatePopupPosition = () => {
  if (!triggerRef.value) return
  const rect = triggerRef.value.getBoundingClientRect()
  popupStyle.value = {
    top: rect.top + window.scrollY + rect.height / 2 + 'px',
    left: rect.left - 360 + 'px',
    transform: 'translateY(-50%)'
  }
}

watch(mode, async () => {
  await nextTick()
  updateHeight()
})

onMounted(() => {
  updateHeight()
})

const page = usePage()
</script>

<template>
  <!-- ヘッダー -->
  <div class="fixed top-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-sm border-b border-gray-100 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 h-14 flex items-center justify-between">
      <!-- ロゴ＋ブランド名 -->
      <Link href="/" class="flex items-center gap-2">
        <img src="/images/logo.png" alt="HRI" class="h-8 w-auto" />
        <span class="hidden sm:block text-xs font-semibold tracking-widest uppercase text-slate-700">
          Human Reliability Intelligence
        </span>
      </Link>

      <!-- 右側：言語切替＋戻るリンク -->
      <div class="flex items-center gap-3">
        <LanguageSwitcher :dark="false" />
        <Link
          href="/"
          class="text-sm text-gray-500 hover:text-gray-800 transition flex items-center gap-1"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          <span class="hidden sm:inline">{{ $t('login.backToHome') }}</span>
        </Link>
      </div>
    </div>
  </div>

  <AuthLayout
    :centered="true"
    :title="leftContent.title"
    :description="leftContent.description"
    :image="leftContent.image"
    :gradient="leftContent.gradient"
  >
    <!-- スマホ用：上部中央にロゴ表示 -->
    <div class="flex flex-col items-center mb-6 md:hidden pt-2">
      <img src="/images/logo.png" alt="HRI" class="h-14 w-auto mb-2" />
      <span class="text-xs font-semibold tracking-widest uppercase text-slate-600">
        Human Reliability Intelligence
      </span>
    </div>

    <!-- タイトル -->
    <h2 class="text-2xl font-semibold mb-8 text-gray-800">
      {{ $t('login.title') }}
    </h2>

    <!-- タブ：Individu（個人）が左 -->
    <div class="relative flex bg-gray-200 rounded-full p-1 mb-10 overflow-hidden">
      <div
        class="absolute top-1 bottom-1 left-1 w-[calc(50%-4px)] bg-white rounded-full shadow transition-all duration-300"
        :class="mode === 'individual' ? 'left-1' : 'left-1/2'"
      ></div>

      <button
        @click="mode = 'individual'"
        class="relative z-10 flex-1 py-2 text-sm font-medium transition"
        :class="mode === 'individual' ? 'text-black' : 'text-gray-500'"
      >
        <UserIcon class="w-4 h-4 inline mr-1" />
        {{ $t('login.tabIndividual') }}
      </button>

      <button
        @click="mode = 'company'"
        class="relative z-10 flex-1 py-2 text-sm font-medium transition"
        :class="mode === 'company' ? 'text-black' : 'text-gray-500'"
      >
        <BuildingOffice2Icon class="w-4 h-4 inline mr-1" />
        {{ $t('login.tabCompany') }}
      </button>
    </div>

    <!-- AUTO HEIGHT WRAPPER -->
    <div
      :style="{ height: wrapperHeight }"
      class="relative transition-all duration-300 ease-in-out"
    >
      <Transition name="fade" mode="out-in" @after-enter="updateHeight">

        <!-- 個人（Google ログイン） -->
        <div
          v-if="mode === 'individual'"
          key="individual"
          ref="activeForm"
          class="space-y-6 absolute w-full"
        >
          <button
            @click="loginGoogle"
            class="w-full flex items-center justify-center gap-3 border border-gray-300 py-3 rounded-xl hover:bg-gray-50 transition mb-8"
          >
            <img src="https://www.svgrepo.com/show/475656/google-color.svg" class="w-5 h-5" />
            {{ $t('login.loginGoogle') }}
          </button>

          <div class="relative group inline-block z-30">
            <button
              type="button"
              ref="triggerRef"
              @mouseenter="handleMouseEnter"
              @mouseleave="handleMouseLeave"
              @click="toggleInfo"
              class="text-sm font-medium text-gray-700 hover:text-black transition flex items-center gap-2"
            >
              <ShieldCheckIcon class="w-4 h-4" />
              {{ $t('login.whyHri') }}
            </button>

            <!-- PCホバーポップアップ -->
            <Teleport to="body">
              <Transition name="popup">
                <div
                  v-if="showDesktopInfo"
                  class="fixed z-[9999] hidden md:block"
                  :style="popupStyle"
                  @mouseenter="clearHoverTimeout"
                  @mouseleave="handleMouseLeave"
                >
                  <div class="relative w-[340px] rounded-2xl border border-gray-200 bg-white shadow-2xl p-5">
                    <div class="space-y-4 text-sm text-gray-700">
                      <div class="flex items-center gap-2 font-semibold text-gray-900">
                        <ShieldCheckIcon class="w-6 h-6 text-black" />
                        <span class="text-md">{{ $t('login.popup.heading') }}</span>
                      </div>
                      <div class="space-y-5">
                        <div class="flex gap-3">
                          <CheckCircleIcon class="w-5 h-5 text-black mt-0.5 shrink-0" />
                          <div>
                            <p class="font-medium text-gray-900">{{ $t('login.popup.trust') }}</p>
                            <p class="text-gray-600">{{ $t('login.popup.trustDesc') }}</p>
                          </div>
                        </div>
                        <div class="flex gap-3">
                          <IdentificationIcon class="w-5 h-5 text-black mt-0.5 shrink-0" />
                          <div>
                            <p class="font-medium text-gray-900">{{ $t('login.popup.controlled') }}</p>
                            <p class="text-gray-600">{{ $t('login.popup.controlledDesc') }}</p>
                          </div>
                        </div>
                        <div class="flex gap-3">
                          <CheckCircleIcon class="w-5 h-5 text-black mt-0.5 shrink-0" />
                          <div>
                            <p class="font-medium text-gray-900">{{ $t('login.popup.history') }}</p>
                            <p class="text-gray-600">{{ $t('login.popup.historyDesc') }}</p>
                          </div>
                        </div>
                      </div>
                      <div class="border-t pt-3 space-y-2">
                        <p class="font-medium text-gray-900 flex items-center gap-2">
                          <ScaleIcon class="w-4 h-4 text-black" />
                          {{ $t('login.popup.compliance') }}
                        </p>
                        <ul class="space-y-1 text-gray-600 text-xs">
                          <li>{{ $t('login.popup.item1') }}</li>
                          <li>{{ $t('login.popup.item2') }}</li>
                          <li>{{ $t('login.popup.item3') }}</li>
                        </ul>
                      </div>
                      <div class="pt-2">
                        <a href="#" class="text-black font-medium hover:underline text-sm">
                          {{ $t('login.popup.consult') }}
                        </a>
                      </div>
                    </div>
                  </div>
                </div>
              </Transition>
            </Teleport>

            <!-- モバイル用モーダル -->
            <div
              v-if="showMobileInfo"
              class="fixed inset-0 flex items-center justify-center z-50 md:hidden"
            >
              <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="closeMobileInfo"></div>
              <div class="relative w-[90%] max-w-sm rounded-2xl bg-white shadow-2xl p-6">
                <button class="absolute top-3 right-3 text-gray-400 hover:text-black text-lg" @click="closeMobileInfo">✕</button>
                <div class="space-y-4 text-sm text-gray-700">
                  <div class="flex items-center gap-2 font-semibold text-gray-900">
                    <ShieldCheckIcon class="w-5 h-5 text-black" />
                    {{ $t('login.popup.heading') }}
                  </div>
                  <div class="space-y-3">
                    <div class="flex gap-3">
                      <CheckCircleIcon class="w-5 h-5 text-black mt-0.5 shrink-0" />
                      <div>
                        <p class="font-medium text-gray-900">{{ $t('login.popup.trust') }}</p>
                        <p class="text-gray-600">{{ $t('login.popup.trustDesc') }}</p>
                      </div>
                    </div>
                    <div class="flex gap-3">
                      <IdentificationIcon class="w-5 h-5 text-black mt-0.5 shrink-0" />
                      <div>
                        <p class="font-medium text-gray-900">{{ $t('login.popup.controlled') }}</p>
                        <p class="text-gray-600">{{ $t('login.popup.controlledDesc') }}</p>
                      </div>
                    </div>
                    <div class="flex gap-3">
                      <CheckCircleIcon class="w-5 h-5 text-black mt-0.5 shrink-0" />
                      <div>
                        <p class="font-medium text-gray-900">{{ $t('login.popup.history') }}</p>
                        <p class="text-gray-600">{{ $t('login.popup.historyDesc') }}</p>
                      </div>
                    </div>
                  </div>
                  <div class="border-t pt-3 space-y-2">
                    <p class="font-medium text-gray-900 flex items-center gap-2">
                      <ScaleIcon class="w-4 h-4 text-black" />
                      {{ $t('login.popup.compliance') }}
                    </p>
                    <ul class="space-y-1 text-gray-600 text-xs">
                      <li>{{ $t('login.popup.item1') }}</li>
                      <li>{{ $t('login.popup.item2') }}</li>
                      <li>{{ $t('login.popup.item3') }}</li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 企業（メール＋パスワード） -->
        <form
          v-else
          key="company"
          ref="activeForm"
          @submit.prevent="submit"
          class="space-y-6 absolute w-full"
        >
          <div class="relative">
            <EnvelopeIcon class="w-5 h-5 absolute left-3 top-3 text-gray-400" />
            <input
              v-model="form.email"
              type="email"
              :placeholder="$t('login.emailPlaceholder')"
              class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-black focus:outline-none bg-white"
            />
          </div>

          <div class="relative">
            <LockClosedIcon class="w-5 h-5 absolute left-3 top-3 text-gray-400" />
            <input
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              :placeholder="$t('login.passwordPlaceholder')"
              class="w-full pl-10 pr-10 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-black focus:outline-none bg-white"
            />
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute right-3 top-3 text-gray-400"
            >
              <EyeIcon v-if="!showPassword" class="w-5 h-5" />
              <EyeSlashIcon v-else class="w-5 h-5" />
            </button>
          </div>

          <button
            type="submit"
            class="w-full bg-red-700 text-white py-3 rounded-xl transition hover:bg-red-800"
          >
            {{ $t('login.loginBtn') }}
          </button>

          <p class="text-center text-sm text-gray-500">
            {{ $t('login.noAccount') }}
            <Link
              :href="route('register.company')"
              class="text-black font-medium hover:underline"
              :preserve-state="false"
              :preserve-scroll="false"
              replace
            >
              {{ $t('login.register') }}
            </Link>
          </p>
        </form>

      </Transition>
    </div>
  </AuthLayout>
</template>

<style>
.zoom-bg {
  animation: slowZoom 20s ease-in-out infinite alternate;
  will-change: transform;
}
@keyframes slowZoom {
  from { transform: scale(1); }
  to { transform: scale(1.08); }
}
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
.popup-enter-active,
.popup-leave-active {
  transition: opacity 0.2s ease, transform 0.2s ease;
}
.popup-enter-from {
  opacity: 0;
  transform: translateX(10px);
}
.popup-leave-to {
  opacity: 0;
  transform: translateX(10px);
}
</style>