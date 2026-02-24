<script setup>
import { ref, computed, nextTick, watch, onMounted, onBeforeUnmount } from 'vue'
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

const mode = ref('company')
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
      title: 'Layanan Khusus Perusahaan',
      description:
        'Daftarkan perusahaan Anda pada layanan HRI dan wujudkan proses rekrutmen yang aman dan terpercaya.',
      image:
        'https://images.unsplash.com/photo-1522202176988-66273c2fd55f',
      gradient: 'from-black/80 via-black/60 to-black/40'
    }
  } else {
    return {
      title: 'Layanan Khusus Pelamar',
      description:
        'Ciptakan proses rekrutmen yang lebih aman. Dengan akun HRI, Anda dapat mengajukan/verifikasi data dan membagikan HRI-ID secara terkendali kepada perusahaan—tanpa QR publik & pencarian eksploratif.',
      image:
        'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4',
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
  if (hoverTimeout) {
    clearTimeout(hoverTimeout)
  }
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
    left: rect.left - 360 + 'px', // 340 width + margin
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
  <AuthLayout 
    :centered="true"
    :title="leftContent.title"
    :description="leftContent.description"
    :image="leftContent.image"
    :gradient="leftContent.gradient"
  >

    <h2 class="text-2xl font-semibold mb-8 text-gray-800">
      Masuk Akun
    </h2>
        <div class="relative flex bg-gray-200 rounded-full p-1 mb-10 overflow-hidden">

          <!-- Indicator -->
          <div
              class="absolute top-1 bottom-1 left-1 w-[calc(50%-4px)] bg-white rounded-full shadow transition-all duration-300"
              :class="mode === 'individual' ? 'left-1/2' : 'left-1'"
          ></div>

          <button
              @click="mode = 'company'"
              class="relative z-10 flex-1 py-2 text-sm font-medium transition"
              :class="mode === 'company' ? 'text-black' : 'text-gray-500'"
          >
              <BuildingOffice2Icon class="w-4 h-4 inline mr-1" />
              Perusahaan
          </button>

          <button
              @click="mode = 'individual'"
              class="relative z-10 flex-1 py-2 text-sm font-medium transition"
              :class="mode === 'individual' ? 'text-black' : 'text-gray-500'"
          >
          
              <UserIcon class="w-4 h-4 inline mr-1" />
              Individu
          </button>

      </div>

      <!-- AUTO HEIGHT WRAPPER -->
      <div
        :style="{ height: wrapperHeight }"
        class="relative transition-all duration-300 ease-in-out"
      >
        <Transition name="fade" mode="out-in" @after-enter="updateHeight">

          <!-- COMPANY -->
          <form
            v-if="mode === 'company'"
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
                placeholder="Email perusahaan"
                class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-black focus:outline-none bg-white"
              />
            </div>

            <div class="relative">
              <LockClosedIcon class="w-5 h-5 absolute left-3 top-3 text-gray-400" />
              <input
                v-model="form.password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="Password"
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
              Login
            </button>

            <p class="text-center text-sm text-gray-500"> Belum punya akun? <Link :href="route('register.company')" class="text-black font-medium hover:underline" :preserve-state="false" :preserve-scroll="false" replace> Daftar </Link> </p>
          </form>

          <div
            v-else
            key="individual"
            ref="activeForm"
            class="space-y-6"
          >
              <button
                  @click="loginGoogle"
                  class="w-full flex items-center justify-center gap-3 border border-gray-300 py-3 rounded-xl hover:bg-gray-50 transition mb-8"
              >
                  <img
                  src="https://www.svgrepo.com/show/475656/google-color.svg"
                  class="w-5 h-5"
                  />
                  Login dengan Google
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
                Kenapa Buat Akun HRI?
              </button>

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
                          <span class="text-md font-bolder">Terverifikasi & Aman</span> 
                        </div>
  
                        <div class="space-y-5">
  
                        <div class="flex gap-3">
                          <CheckCircleIcon class="w-5 h-5 text-black mt-0.5" />
                          <div>
                            <p class="font-medium text-gray-900">Lebih dipercaya</p>
                            <p class="text-gray-600">
                              Verifikasi pihak ketiga untuk pendidikan, pekerjaan, lisensi.
                            </p>
                          </div>
                        </div>
  
                        <div class="flex gap-3">
                          <IdentificationIcon class="w-5 h-5 text-black mt-0.5" />
                          <div>
                            <p class="font-medium text-gray-900">Bagikan terkendali</p>
                            <p class="text-gray-600">
                              Perusahaan hanya bisa cek via HRI-ID dengan izin Anda.
                            </p>
                          </div>
                        </div>
  
                        <div class="flex gap-3">
                          <CheckCircleIcon class="w-5 h-5 text-black mt-0.5" />
                          <div>
                            <p class="font-medium text-gray-900">Riwayat jelas</p>
                            <p class="text-gray-600">
                              Status cocok/tidak & tanggal verifikasi terakhir.
                            </p>
                          </div>
                        </div>
  
                      </div>
  
                      <div class="border-t pt-3 space-y-2">
                        <p class="font-medium text-gray-900 flex items-center gap-2">
                          <ScaleIcon class="w-4 h-4 text-black" />
                          Kepatuhan & Perlindungan
                        </p>
  
                        <ul class="space-y-1 text-gray-600 text-xs">
                          <li>• Enkripsi & kontrol akses berbasis peran</li>
                          <li>• Patuh PDP Law (ID)</li>
                          <li>• Retensi maksimal 3 tahun</li>
                        </ul>
                      </div>
  
                      <div class="pt-2">
                        <a
                          href="#"
                          class="text-black font-medium hover:underline text-sm"
                        >
                          Konsultasi gratis →
                        </a>
                      </div>
  
                      </div>
                    </div>
                  </div>
                </Transition>
              </Teleport>

              <div
                v-if="showMobileInfo"
                class="fixed inset-0 flex items-center justify-center z-50 md:hidden"
              >
                <div
                  class="absolute inset-0 bg-black/40 backdrop-blur-sm"
                  @click="closeMobileInfo"
                ></div>

                <div class="relative w-[90%] max-w-sm rounded-2xl bg-white shadow-2xl p-6 animate-fadeIn">

                  <button
                    class="absolute top-3 right-3 text-gray-400 hover:text-black"
                    @click="closeMobileInfo"
                  >
                    ✕
                  </button>

                  <div class="space-y-4 text-sm text-gray-700">

                    <div class="flex items-center gap-2 font-semibold text-gray-900">
                      <ShieldCheckIcon class="w-5 h-5 text-black" />
                      Terverifikasi & Aman
                    </div>

                    <div class="space-y-3">

                      <div class="flex gap-3">
                        <CheckCircleIcon class="w-5 h-5 text-black mt-0.5" />
                        <div>
                          <p class="font-medium text-gray-900">Lebih dipercaya</p>
                          <p class="text-gray-600">
                            Verifikasi pihak ketiga untuk pendidikan, pekerjaan, lisensi.
                          </p>
                        </div>
                      </div>

                      <div class="flex gap-3">
                        <IdentificationIcon class="w-5 h-5 text-black mt-0.5" />
                        <div>
                          <p class="font-medium text-gray-900">Bagikan terkendali</p>
                          <p class="text-gray-600">
                            Perusahaan hanya bisa cek via HRI-ID dengan izin Anda.
                          </p>
                        </div>
                      </div>

                      <div class="flex gap-3">
                        <CheckCircleIcon class="w-5 h-5 text-black mt-0.5" />
                        <div>
                          <p class="font-medium text-gray-900">Riwayat jelas</p>
                          <p class="text-gray-600">
                            Status cocok/tidak & tanggal verifikasi terakhir.
                          </p>
                        </div>
                      </div>

                    </div>

                    <div class="border-t pt-3 space-y-2">
                      <p class="font-medium text-gray-900 flex items-center gap-2">
                        <ScaleIcon class="w-4 h-4 text-black" />
                        Kepatuhan & Perlindungan
                      </p>

                      <ul class="space-y-1 text-gray-600 text-xs">
                        <li>• Enkripsi & kontrol akses berbasis peran</li>
                        <li>• Patuh PDP Law (ID)</li>
                        <li>• Retensi maksimal 3 tahun</li>
                      </ul>
                    </div>

                  </div>
                </div>
              </div>

            </div>

          </div>

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

.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.35s ease;
}
.slide-fade-enter-from {
  opacity: 0;
  transform: translateY(20px);
}
.slide-fade-leave-to {
  opacity: 0;
  transform: translateY(-20px);
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