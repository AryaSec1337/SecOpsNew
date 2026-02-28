<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SecOps Command Center - Access Control</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;700;900&family=Rajdhani:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Rajdhani', sans-serif;
            background-color: #020617; /* Slate 950 */
            overflow: hidden;
        }
        .font-cyber {
            font-family: 'Orbitron', sans-serif;
        }
        .glass-panel {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(56, 189, 248, 0.1);
            box-shadow: 0 0 40px rgba(56, 189, 248, 0.1);
        }
        .input-group:focus-within label {
            color: #38bdf8; /* Sky 400 */
        }
        .input-group:focus-within svg {
            color: #38bdf8;
            filter: drop-shadow(0 0 5px rgba(56,189,248,0.5));
        }
        .input-cyber {
            transition: all 0.3s ease;
        }
        .input-cyber:focus {
            box-shadow: 0 0 15px rgba(56, 189, 248, 0.3);
            border-color: #38bdf8;
        }
        /* Custom Scrollbar for any overflow */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #0f172a;
        }
        ::-webkit-scrollbar-thumb {
            background: #1e293b;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #334155;
        }
        
        /* Glitch Effect Keyframes */
        @keyframes glitch {
            0% { transform: translate(0); }
            20% { transform: translate(-2px, 2px); }
            40% { transform: translate(-2px, -2px); }
            60% { transform: translate(2px, 2px); }
            80% { transform: translate(2px, -2px); }
            100% { transform: translate(0); }
        }
        .animate-glitch:hover {
            animation: glitch 0.3s cubic-bezier(.25, .46, .45, .94) both infinite;
            color: #38bdf8;
        }
    </style>
</head>
<body class="h-screen w-screen flex items-center justify-center relative">

    <!-- Background Canvas for Particles -->
    <canvas id="cyber-canvas" class="absolute inset-0 z-0"></canvas>
    
    <!-- Overlay Gradient -->
    <div class="absolute inset-0 z-0 bg-gradient-to-b from-slate-950/80 via-slate-950/40 to-slate-950/90 pointer-events-none"></div>

    <!-- Main Container -->
    <div class="relative z-10 w-full max-w-6xl h-full lg:h-auto flex flex-col lg:flex-row items-center justify-center p-4 lg:p-0 gap-8 lg:gap-20">
        
        <!-- Left Section: Brand & Visuals -->
        <div class="hidden lg:flex flex-col flex-1 items-start text-left space-y-6 animate-fade-in-left">
            <div class="flex items-center gap-4 mb-4">
                <div class="relative">
                    <div class="absolute inset-0 bg-blue-500 blur-xl opacity-20 rounded-full animate-pulse"></div>
                    <img src="{{ asset('images/mega_insurance_logo.png') }}" alt="Mega Insurance Logo" class="relative w-32 h-auto object-contain drop-shadow-xl">
                </div>
                <div>
                    <h1 class="text-5xl font-cyber font-black text-transparent bg-clip-text bg-gradient-to-r from-blue-400 via-sky-300 to-indigo-400 tracking-wider drop-shadow-lg">
                        SECOPS
                    </h1>
                    <div class="flex items-center gap-2 mt-1">
                        <div class="h-0.5 w-8 bg-blue-500"></div>
                        <span class="text-blue-300 tracking-[0.3em] text-sm font-semibold uppercase">Mega Insurance</span>
                    </div>
                </div>
            </div>
            
            <p class="text-slate-400 text-lg leading-relaxed max-w-lg border-l-2 border-blue-500/30 pl-6">
                Advanced Threat Intelligence & Incident Response Platform.
                <br>
                <span class="text-sky-400 font-medium">Monitoring. Detection. Neutralization.</span>
            </p>

            <!-- Stats / Info Grid -->
            <div class="grid grid-cols-2 gap-4 w-full max-w-md mt-8">
                <div class="glass-panel p-4 rounded-xl border-l-4 border-l-green-500">
                    <div class="text-xs text-slate-500 uppercase tracking-wider">System Status</div>
                    <div class="text-green-400 font-cyber text-lg flex items-center gap-2">
                        <span class="relative flex h-2 w-2">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                        </span>
                        OPERATIONAL
                    </div>
                </div>
                <div class="glass-panel p-4 rounded-xl border-l-4 border-l-blue-500">
                    <div class="text-xs text-slate-500 uppercase tracking-wider">Active Agents</div>
                    <div class="text-blue-400 font-cyber text-lg">CONNECTED</div>
                </div>
            </div>
        </div>

        <!-- Right Section: Login Form -->
        <div class="w-full max-w-md glass-panel p-8 sm:p-10 rounded-2xl relative overflow-hidden animate-fade-in-up shadow-2xl shadow-blue-900/20">
            <!-- Decorative Top Border -->
            <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-blue-500 to-transparent opacity-50"></div>
            
            <div class="mb-8 text-center lg:text-left relative">
                <h2 class="text-3xl font-bold text-white mb-2 font-cyber tracking-wide">AUTHENTICATION</h2>
                <p class="text-slate-400 text-sm">Enter your credentials to access the secure terminal.</p>
                <!-- Decorative element -->
                <div class="absolute top-0 right-0 -mt-2 -mr-2 w-16 h-16 border-t-2 border-r-2 border-blue-500/20 rounded-tr-xl"></div>
            </div>

            <form action="{{ route('login') }}" method="POST" class="space-y-6">
                @csrf
                
                <div class="space-y-2 input-group">
                    <label for="email" class="text-xs font-bold text-slate-500 uppercase tracking-widest transition-colors">Corporate ID</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-colors">
                            <svg class="h-5 w-5 text-slate-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <input type="email" name="email" id="email" required autocomplete="off"
                            class="input-cyber block w-full pl-12 pr-4 py-3.5 bg-slate-900/50 border border-slate-700/50 rounded-xl text-slate-200 placeholder-slate-600 focus:outline-none focus:ring-0 transition-all font-mono" 
                            placeholder="agent@secops.internal">
                    </div>
                </div>

                <div class="space-y-2 input-group">
                    <label for="password" class="text-xs font-bold text-slate-500 uppercase tracking-widest transition-colors">Access Key</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none transition-colors">
                            <svg class="h-5 w-5 text-slate-600 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input type="password" name="password" id="password" required 
                            class="input-cyber block w-full pl-12 pr-4 py-3.5 bg-slate-900/50 border border-slate-700/50 rounded-xl text-slate-200 placeholder-slate-600 focus:outline-none focus:ring-0 transition-all font-mono" 
                            placeholder="••••••••">
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <label class="flex items-center space-x-2 cursor-pointer group">
                        <input type="checkbox" name="remember" class="form-checkbox h-4 w-4 text-blue-500 rounded border-slate-700 bg-slate-900/50 focus:ring-offset-slate-900 focus:ring-blue-500 transition-all">
                        <span class="text-sm text-slate-400 group-hover:text-blue-400 transition-colors">Keep Session Active</span>
                    </label>
                    <a href="#" class="text-sm text-blue-500 hover:text-blue-400 hover:underline decoration-blue-500/30 underline-offset-4 transition-all">Recovery?</a>
                </div>

                <button type="submit" class="group relative w-full flex justify-center items-center py-4 px-4 border border-transparent rounded-xl text-sm font-bold text-white overflow-hidden transition-all duration-300 transform hover:-translate-y-1 hover:shadow-lg hover:shadow-blue-500/25">
                    <!-- Button Background Gradient -->
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-700 via-indigo-600 to-blue-700 opacity-90 group-hover:opacity-100 transition-opacity bg-[length:200%_auto] animate-gradient-x"></div>
                    <!-- Button Glow Effect -->
                    <div class="absolute -inset-1 bg-gradient-to-r from-blue-600 to-cyan-400 rounded-xl blur opacity-20 group-hover:opacity-40 transition opacity duration-200"></div>
                    
                    <span class="relative flex items-center gap-2 tracking-widest font-cyber uppercase">
                        Initiate Login
                        <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path></svg>
                    </span>
                </button>
            </form>

            <div class="mt-8 text-center">
                <p class="text-[10px] text-slate-600 uppercase tracking-widest">Authorized Personnel Only</p>
                <div class="w-16 h-0.5 bg-slate-800 mx-auto mt-2"></div>
            </div>
        </div>
    </div>

    <!-- SweetAlert2 for Errors -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        @if ($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'ACCESS DENIED',
                text: '{{ $errors->first() }}',
                background: '#0f172a',
                color: '#e2e8f0',
                confirmButtonColor: '#ef4444',
                iconColor: '#ef4444',
                customClass: {
                    popup: 'border border-red-900/50 rounded-xl shadow-2xl backdrop-blur-xl'
                }
            });
        @endif

        // Particle Animation Script
        const canvas = document.getElementById('cyber-canvas');
        const ctx = canvas.getContext('2d');
        let width, height;
        let particles = [];
        const particleCount = 60; // Adjust for density
        const connectionDistance = 150;

        function resize() {
            width = canvas.width = window.innerWidth;
            height = canvas.height = window.innerHeight;
        }
        window.addEventListener('resize', resize);
        resize();

        class Particle {
            constructor() {
                this.x = Math.random() * width;
                this.y = Math.random() * height;
                this.vx = (Math.random() - 0.5) * 0.5;
                this.vy = (Math.random() - 0.5) * 0.5;
                this.size = Math.random() * 2 + 1;
                this.color = `rgba(56, 189, 248, ${Math.random() * 0.5 + 0.1})`; // Sky blue-ish
            }

            update() {
                this.x += this.vx;
                this.y += this.vy;

                if (this.x < 0) this.x = width;
                if (this.x > width) this.x = 0;
                if (this.y < 0) this.y = height;
                if (this.y > height) this.y = 0;
            }

            draw() {
                ctx.fillStyle = this.color;
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                ctx.fill();
            }
        }

        function initParticles() {
            particles = [];
            for (let i = 0; i < particleCount; i++) {
                particles.push(new Particle());
            }
        }

        function animate() {
            ctx.clearRect(0, 0, width, height);

            // Draw connections first
            ctx.lineWidth = 0.5;
            for (let i = 0; i < particles.length; i++) {
                for (let j = i + 1; j < particles.length; j++) {
                    const dx = particles[i].x - particles[j].x;
                    const dy = particles[i].y - particles[j].y;
                    const dist = Math.sqrt(dx * dx + dy * dy);

                    if (dist < connectionDistance) {
                        const opacity = 1 - (dist / connectionDistance);
                        ctx.strokeStyle = `rgba(56, 189, 248, ${opacity * 0.2})`; // Faint connections
                        ctx.beginPath();
                        ctx.moveTo(particles[i].x, particles[i].y);
                        ctx.lineTo(particles[j].x, particles[j].y);
                        ctx.stroke();
                    }
                }
            }

            // Update and draw particles
            particles.forEach(p => {
                p.update();
                p.draw();
            });

            requestAnimationFrame(animate);
        }

        initParticles();
        animate();
    </script>
</body>
</html>
