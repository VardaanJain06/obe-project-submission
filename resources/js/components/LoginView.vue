<template>
  <div class="min-h-screen bg-slate-950 flex flex-col justify-center py-12 sm:px-6 lg:px-8 relative overflow-hidden font-sans selection:bg-indigo-500 selection:text-white"
       @mousemove="handleMouseMove" @mouseleave="handleMouseLeave">
    
    <!-- 🌊 SILK WAVES INTERACTIVE CANVAS BACKGROUND -->
    <canvas ref="silkCanvas" class="absolute inset-0 w-full h-full pointer-events-none z-0"></canvas>
    
    <!-- Ambient Depth Background Overlays -->
    <div class="absolute inset-0 bg-gradient-to-b from-slate-950/40 via-transparent to-slate-950/80 pointer-events-none z-0"></div>
    <div class="absolute inset-0 bg-ambient-grid opacity-30 pointer-events-none z-0"></div>

    <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10 text-center space-y-3">
      <!-- Dynamic Login Logo / Icon -->
      <div class="inline-flex w-16 h-16 rounded-2xl bg-gradient-to-tr from-indigo-600 via-blue-500 to-emerald-400 p-[2px] shadow-2xl shadow-indigo-500/30 backdrop-blur">
        <div class="w-full h-full bg-slate-950/90 rounded-[14px] flex items-center justify-center p-1.5 overflow-hidden">
          <img v-if="branding.login_logo" :src="branding.login_logo" class="w-full h-full object-contain rounded-lg" alt="Portal Logo" />
          <div v-else class="font-black text-indigo-400 text-2xl">
            {{ branding.app_short_name || 'NBA' }}
          </div>
        </div>
      </div>

      <h2 class="text-3xl font-extrabold tracking-tight text-white drop-shadow-md">
        {{ branding.app_name || 'NBA SAR Platform' }}
      </h2>
      <p class="text-xs text-slate-300 max-w-xs mx-auto">
        {{ branding.app_tagline || 'Outcome-Based Education & National Board of Accreditation Enterprise Platform' }}
      </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md relative z-10">
      <div class="glass-card py-8 px-6 sm:px-10 rounded-2xl border border-slate-700/60 shadow-2xl bg-slate-900/85 backdrop-blur-2xl">
        <!-- Error Alert -->
        <div v-if="errorMessage" class="mb-5 p-3 rounded-lg bg-rose-500/10 border border-rose-500/30 text-rose-300 text-xs flex items-center gap-2">
          <span>⚠️</span>
          <span>{{ errorMessage }}</span>
        </div>

        <form @submit.prevent="handleLogin" class="space-y-4">
          <div>
            <label class="block text-xs font-semibold text-slate-300">Email Address</label>
            <div class="mt-1">
              <input type="email" v-model="form.email" required placeholder="name@institution.edu"
                     class="w-full bg-slate-950/80 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-600 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition" />
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-300">Password</label>
            <div class="mt-1">
              <input type="password" v-model="form.password" required placeholder="Enter your password"
                     class="w-full bg-slate-950/80 border border-slate-800 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-600 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 outline-none transition" />
            </div>
          </div>

          <div class="flex items-center justify-between text-xs">
            <label class="flex items-center text-slate-400 cursor-pointer">
              <input type="checkbox" v-model="form.remember" class="rounded bg-slate-950 border-slate-800 text-indigo-600 focus:ring-0 mr-2" />
              Remember me
            </label>
            <span class="text-slate-500">Encrypted Session</span>
          </div>

          <div class="pt-2">
            <button type="submit" :disabled="loading"
                    class="w-full py-2.5 px-4 rounded-xl text-white bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-500 hover:to-blue-500 text-sm font-bold shadow-lg shadow-indigo-600/30 transition disabled:opacity-50 flex items-center justify-center gap-2">
              <span v-if="loading" class="w-4 h-4 border-2 border-white/20 border-t-white rounded-full animate-spin"></span>
              <span>{{ loading ? 'Authenticating...' : 'Sign In to Dashboard' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  name: 'LoginView',
  data() {
    return {
      loading: false,
      errorMessage: '',
      branding: {
        app_name: 'NBA SAR & OBE Platform',
        app_short_name: 'NBA',
        app_tagline: 'Outcome-Based Education & National Board of Accreditation Enterprise Platform',
        login_logo: '',
      },
      form: {
        email: '',
        password: '',
        remember: true,
      },
      mouse: { x: 0.5, y: 0.5, targetX: 0.5, targetY: 0.5 },
      animationFrameId: null,
      time: 0,
    };
  },
  mounted() {
    this.fetchBranding();
    this.initSilkWaves();
  },
  beforeUnmount() {
    if (this.animationFrameId) {
      cancelAnimationFrame(this.animationFrameId);
    }
    window.removeEventListener('resize', this.onResize);
  },
  methods: {
    async fetchBranding() {
      try {
        const res = await axios.get('/api/v1/settings');
        if (res.data && res.data.success) {
          this.branding = { ...this.branding, ...res.data.data };
          if (this.branding.favicon) {
            let link = document.querySelector("link[rel~='icon']");
            if (link) link.href = this.branding.favicon;
          }
        }
      } catch (err) {}
    },
    async handleLogin() {
      this.loading = true;
      this.errorMessage = '';
      try {
        const response = await axios.post('/api/v1/auth/login', {
          email: this.form.email,
          password: this.form.password,
          remember: this.form.remember,
        });

        if (response.data && response.data.success) {
          this.$emit('authenticated', response.data.user);
        } else {
          this.errorMessage = response.data.message || 'Login failed.';
        }
      } catch (error) {
        this.errorMessage = error.response?.data?.message || 'Invalid credentials or server error.';
      } finally {
        this.loading = false;
      }
    },
    handleMouseMove(e) {
      this.mouse.targetX = e.clientX / window.innerWidth;
      this.mouse.targetY = e.clientY / window.innerHeight;
    },
    handleMouseLeave() {
      this.mouse.targetX = 0.5;
      this.mouse.targetY = 0.5;
    },
    initSilkWaves() {
      const canvas = this.$refs.silkCanvas;
      if (!canvas) return;

      const ctx = canvas.getContext('2d');
      const dpr = Math.min(window.devicePixelRatio || 1, 2);

      const resize = () => {
        canvas.width = window.innerWidth * dpr;
        canvas.height = window.innerHeight * dpr;
        ctx.scale(dpr, dpr);
      };
      this.onResize = resize;
      window.addEventListener('resize', resize);
      resize();

      // Multi-ribbon Silk Configuration
      const ribbons = [
        {
          baseY: 0.28,
          speed: 0.008,
          amplitude: 80,
          freq: 0.0018,
          phase: 0,
          colorA: 'rgba(99, 102, 241, 0.45)', // Indigo
          colorB: 'rgba(6, 182, 212, 0.0)',   // Cyan
          lineWidth: 2.5,
          subWaves: 5,
        },
        {
          baseY: 0.48,
          speed: 0.006,
          amplitude: 95,
          freq: 0.0014,
          phase: Math.PI / 3,
          colorA: 'rgba(59, 130, 246, 0.4)',  // Blue
          colorB: 'rgba(16, 185, 129, 0.0)',  // Emerald
          lineWidth: 2.0,
          subWaves: 6,
        },
        {
          baseY: 0.68,
          speed: 0.007,
          amplitude: 110,
          freq: 0.0016,
          phase: Math.PI * 0.75,
          colorA: 'rgba(139, 92, 246, 0.35)', // Purple
          colorB: 'rgba(99, 102, 241, 0.0)',  // Indigo
          lineWidth: 2.2,
          subWaves: 5,
        },
        {
          baseY: 0.85,
          speed: 0.005,
          amplitude: 75,
          freq: 0.002,
          phase: Math.PI * 1.2,
          colorA: 'rgba(16, 185, 129, 0.3)',  // Emerald
          colorB: 'rgba(6, 182, 212, 0.0)',   // Cyan
          lineWidth: 1.8,
          subWaves: 4,
        },
      ];

      const render = () => {
        this.time += 1;

        // Smooth mouse dampening
        this.mouse.x += (this.mouse.targetX - this.mouse.x) * 0.05;
        this.mouse.y += (this.mouse.targetY - this.mouse.y) * 0.05;

        const width = window.innerWidth;
        const height = window.innerHeight;

        ctx.clearRect(0, 0, width, height);

        // Draw each silk ribbon layer
        ribbons.forEach((ribbon, rIdx) => {
          const baseY = height * ribbon.baseY + (this.mouse.y - 0.5) * 60;
          const mouseInfluenceX = (this.mouse.x - 0.5) * 40;

          // Draw multiple parallel strands per ribbon for that realistic 3D woven silk shimmer
          for (let s = 0; s < ribbon.subWaves; s++) {
            const strandOffset = (s - ribbon.subWaves / 2) * 14;
            const wavePhase = ribbon.phase + (this.time * ribbon.speed) + (s * 0.15);

            ctx.beginPath();
            const step = 8;
            for (let x = 0; x <= width + step; x += step) {
              const normalX = x / width;
              
              // Harmonic sine + secondary harmonic for fluid wave dynamics
              const sin1 = Math.sin(x * ribbon.freq + wavePhase + mouseInfluenceX * 0.02);
              const sin2 = Math.cos(x * ribbon.freq * 1.6 - wavePhase * 0.8);
              const envelope = Math.sin(normalX * Math.PI); // Pin at edges, swell in middle

              const y = baseY + strandOffset + (sin1 * ribbon.amplitude * 0.7 + sin2 * ribbon.amplitude * 0.3) * (0.6 + envelope * 0.6);

              if (x === 0) {
                ctx.moveTo(x, y);
              } else {
                ctx.lineTo(x, y);
              }
            }

            // Silk gradient stroke
            const grad = ctx.createLinearGradient(0, baseY - 100, width, baseY + 100);
            grad.addColorStop(0, ribbon.colorB);
            grad.addColorStop(0.3, ribbon.colorA);
            grad.addColorStop(0.7, ribbon.colorA);
            grad.addColorStop(1, ribbon.colorB);

            ctx.strokeStyle = grad;
            ctx.lineWidth = ribbon.lineWidth;
            ctx.stroke();

            // Light translucent wash fill under the primary strand
            if (s === 0) {
              ctx.lineTo(width, height);
              ctx.lineTo(0, height);
              ctx.closePath();
              const fillGrad = ctx.createLinearGradient(0, baseY, 0, height);
              fillGrad.addColorStop(0, ribbon.colorA.replace(/[\d\.]+\)$/, '0.06)'));
              fillGrad.addColorStop(1, 'transparent');
              ctx.fillStyle = fillGrad;
              ctx.fill();
            }
          }
        });

        this.animationFrameId = requestAnimationFrame(render);
      };

      render();
    },
  },
};
</script>
