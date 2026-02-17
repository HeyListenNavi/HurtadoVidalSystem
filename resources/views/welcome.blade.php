<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medica - Retell AI Demo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Montserrat', sans-serif; }
        .btn-animate:active { transform: scale(0.98); transition: transform 0.1s; }
        .glass-card { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); }
    </style>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-6 text-slate-900">

    <div class="max-w-md w-full space-y-8 my-8">
        <!-- Header / Logo -->
        <div class="text-center space-y-4">
            <div class="inline-flex w-16 h-16 bg-emerald-500 rounded-[24px] items-center justify-center text-white text-xl font-bold shadow-lg shadow-emerald-200">
                M
            </div>
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-800">Medica AI Demo</h1>
                <p class="text-xs font-semibold text-emerald-600 uppercase tracking-widest mt-1">Asistente Virtual Inteligente</p>
            </div>
        </div>

        <!-- Main Card -->
        <div class="bg-white p-8 rounded-[40px] border border-slate-200/60 shadow-sm space-y-5">

            <!-- Campo: Teléfono -->
            <div class="space-y-2">
                <label for="phone" class="text-[11px] font-bold text-slate-400 uppercase tracking-widest px-1">Número de Destino</label>
                <div class="relative">
                    <input type="tel" id="phone" placeholder="+52 1..."
                        class="w-full py-4 pl-12 pr-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm outline-none focus:ring-4 focus:ring-emerald-500/10 transition-all font-medium">
                    <svg class="w-5 h-5 absolute left-4 top-4 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.948V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                </div>
            </div>

            <!-- Campo: Nombre Paciente -->
            <div class="space-y-2">
                <label for="patient_name" class="text-[11px] font-bold text-slate-400 uppercase tracking-widest px-1">Nombre del Paciente</label>
                <input type="text" id="patient_name" placeholder="Ej. Juan Pérez"
                    class="w-full py-4 px-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm outline-none focus:ring-4 focus:ring-emerald-500/10 transition-all font-medium">
            </div>

            <!-- Fila: Doctor y Consultorio -->
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label for="doctor_name" class="text-[11px] font-bold text-slate-400 uppercase tracking-widest px-1">Doctor</label>
                    <input type="text" id="doctor_name" placeholder="Ej. Dr. García"
                        class="w-full py-4 px-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm outline-none focus:ring-4 focus:ring-emerald-500/10 transition-all font-medium">
                </div>
                <div class="space-y-2">
                    <label for="clinic_name" class="text-[11px] font-bold text-slate-400 uppercase tracking-widest px-1">Consultorio</label>
                    <input type="text" id="clinic_name" placeholder="Ej. Medica Central"
                        class="w-full py-4 px-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm outline-none focus:ring-4 focus:ring-emerald-500/10 transition-all font-medium">
                </div>
            </div>

            <!-- Fila: Fecha y Hora -->
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label for="appointment_date" class="text-[11px] font-bold text-slate-400 uppercase tracking-widest px-1">Fecha Cita</label>
                    <input type="date" id="appointment_date"
                        class="w-full py-4 px-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm outline-none focus:ring-4 focus:ring-emerald-500/10 transition-all font-medium">
                </div>
                <div class="space-y-2">
                    <label for="appointment_time" class="text-[11px] font-bold text-slate-400 uppercase tracking-widest px-1">Hora Cita</label>
                    <input type="time" id="appointment_time"
                        class="w-full py-4 px-4 bg-slate-50 border border-slate-100 rounded-2xl text-sm outline-none focus:ring-4 focus:ring-emerald-500/10 transition-all font-medium">
                </div>
            </div>

            <button id="call-button" onclick="initiateCall()"
                class="w-full py-5 rounded-[28px] bg-emerald-500 text-white font-bold text-sm shadow-lg shadow-emerald-200/50 btn-animate flex items-center justify-center gap-3 transition-all mt-4">
                <span>Solicitar Llamada</span>
                <svg id="loader" class="hidden animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </button>

            <!-- Status Indicator -->
            <div id="status-container" class="hidden flex items-center gap-3 p-4 rounded-2xl bg-emerald-50 border border-emerald-100">
                <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                <span id="status-text" class="text-[11px] font-bold text-emerald-700 uppercase tracking-tight">Llamada en curso...</span>
            </div>
        </div>

        <!-- Footer Info -->
        <div class="text-center">
            <p class="text-[10px] text-slate-400 font-medium uppercase tracking-widest">Powered by Retell AI & Medica Staff</p>
        </div>
    </div>

    <!-- Alert Message Container -->
    <div id="alert" class="fixed top-6 left-6 right-6 hidden transform transition-all duration-300 translate-y-[-20px] opacity-0 z-[100]">
        <div id="alert-box" class="p-4 rounded-2xl shadow-xl flex items-center justify-between border">
            <span id="alert-text" class="text-sm font-semibold"></span>
            <button onclick="closeAlert()" class="text-slate-400">&times;</button>
        </div>
    </div>

    <script>
        // Configuración de la demo
        const config = {
            apiKey: "{{ env('RETELL_API_KEY') }}",
            agentId: 'agent_b38569dae01a294e4655094525',
            fromNumber: '+18456066291'
        };

        async function initiateCall() {
            const phoneInput = document.getElementById('phone');
            const patientInput = document.getElementById('patient_name');
            const doctorInput = document.getElementById('doctor_name');
            const clinicInput = document.getElementById('clinic_name');
            const dateInput = document.getElementById('appointment_date');
            const timeInput = document.getElementById('appointment_time');

            const callBtn = document.getElementById('call-button');
            const loader = document.getElementById('loader');
            const statusContainer = document.getElementById('status-container');

            const toNumber = phoneInput.value.trim();

            if (!toNumber) {
                showAlert('Por favor, ingresa un número de teléfono.', 'error');
                return;
            }

            // UI Loading state
            callBtn.disabled = true;
            callBtn.classList.add('opacity-80');
            loader.classList.remove('hidden');
            statusContainer.classList.remove('hidden');
            document.getElementById('status-text').innerText = 'Iniciando llamada...';

            try {
                // LLAMADA REAL A LA API DE RETELL
                const response = await fetch('https://api.retellai.com/v2/create-phone-call', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${config.apiKey}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        from_number: config.fromNumber,
                        to_number: toNumber,
                        override_agent_id: config.agentId,
                        retell_llm_dynamic_variables: {
                            nombre_paciente: patientInput.value.trim() || 'Paciente',
                            fecha_cita_iso: dateInput.value || 'hoy',
                            hora_cita_iso: timeInput.value || 'pronto',
                            nombre_doctor: doctorInput.value.trim() || 'Médico de Turno',
                            nombre_consultorio: clinicInput.value.trim() || 'Medica'
                        }
                    })
                });

                const data = await response.json();

                if (response.ok) {
                    showAlert('¡Llamada solicitada con éxito!', 'success');
                    document.getElementById('status-text').innerText = 'Llamada conectada exitosamente';
                } else {
                    throw new Error(data.message || 'Error al iniciar la llamada');
                }

            } catch (error) {
                showAlert(error.message, 'error');
                statusContainer.classList.add('hidden');
            } finally {
                callBtn.disabled = false;
                callBtn.classList.remove('opacity-80');
                loader.classList.add('hidden');
            }
        }

        function showAlert(text, type) {
            const alert = document.getElementById('alert');
            const alertBox = document.getElementById('alert-box');
            const alertText = document.getElementById('alert-text');

            alertText.innerText = text;

            if (type === 'success') {
                alertBox.className = 'p-4 rounded-2xl shadow-xl flex items-center justify-between border bg-white border-emerald-100 text-emerald-700';
            } else {
                alertBox.className = 'p-4 rounded-2xl shadow-xl flex items-center justify-between border bg-white border-red-100 text-red-600';
            }

            alert.classList.remove('hidden');
            setTimeout(() => {
                alert.classList.add('translate-y-0', 'opacity-100');
            }, 10);

            setTimeout(closeAlert, 5000);
        }

        function closeAlert() {
            const alert = document.getElementById('alert');
            alert.classList.remove('translate-y-0', 'opacity-100');
            setTimeout(() => alert.classList.add('hidden'), 300);
        }
    </script>
</body>
</html>
