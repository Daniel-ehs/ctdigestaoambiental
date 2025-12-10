

import React, { useState, useEffect } from 'react';
import Layout from './components/Layout';
import { ElectricityDashboard } from './components/ElectricityDashboard';
import WaterDashboard from './components/WaterDashboard';
import WasteDashboard from './components/WasteDashboard';
import DataImport from './components/DataImport';
import { AppState, ModuleType, UserRole, WaterRecord, ElectricityRecord, WasteRecord, User } from './types';
import { INITIAL_ELECTRICITY, INITIAL_WATER, INITIAL_WASTE, INITIAL_UNITS, INITIAL_GOAL, INITIAL_WATER_GOAL, INITIAL_WASTE_GOAL, INITIAL_USERS } from './constants';
import { Plus, Trash2, Building2, Target, Users, Lock, LogIn, Leaf, Mail, Moon, Sun, Edit2, Info, LayoutGrid, CheckSquare, Square, Eye, EyeOff } from 'lucide-react';

const App: React.FC = () => {
  const [activeModule, setActiveModule] = useState<ModuleType>('electricity');
  
  // Settings Tab State
  const [activeSettingsTab, setActiveSettingsTab] = useState<'goals' | 'users' | 'units' | 'system'>('goals');

  // Theme Management
  const [theme, setTheme] = useState<'light' | 'dark'>(() => {
    if (typeof window !== 'undefined') {
      return localStorage.getItem('theme') as 'light' | 'dark' || 'light';
    }
    return 'light';
  });

  // Data State
  const [data, setData] = useState<AppState>({
    electricity: INITIAL_ELECTRICITY,
    water: INITIAL_WATER,
    waste: INITIAL_WASTE,
    units: INITIAL_UNITS,
    electricityGoal: INITIAL_GOAL,
    waterGoal: INITIAL_WATER_GOAL,
    wasteGoal: INITIAL_WASTE_GOAL,
    users: INITIAL_USERS,
    currentUser: null
  });

  // Local UI State
  const [newUnitName, setNewUnitName] = useState('');
  const [isAuthenticated, setIsAuthenticated] = useState(false);
  
  // Login Form State
  const [loginEmail, setLoginEmail] = useState('');
  const [loginPassword, setLoginPassword] = useState('');
  const [showPassword, setShowPassword] = useState(false);
  const [loginError, setLoginError] = useState('');

  // User Management State (Add/Edit)
  const [editingUserId, setEditingUserId] = useState<string | null>(null);
  const [userFormData, setUserFormData] = useState({ 
    name: '', 
    email: '', 
    password: '', 
    role: 'viewer' as UserRole,
    allowedUnits: [] as string[]
  });

  // Apply Theme Class
  useEffect(() => {
    const root = window.document.documentElement;
    if (theme === 'dark') {
      root.classList.add('dark');
    } else {
      root.classList.remove('dark');
    }
    localStorage.setItem('theme', theme);
  }, [theme]);

  const toggleTheme = () => {
    setTheme(prev => prev === 'light' ? 'dark' : 'light');
  };

  // --- Auth Handlers ---
  const handleLogin = (e: React.FormEvent) => {
    e.preventDefault();
    const user = data.users.find(u => u.email === loginEmail && u.password === loginPassword);
    if (user) {
      setData(prev => ({ ...prev, currentUser: user }));
      setIsAuthenticated(true);
      setLoginError('');
    } else {
      setLoginError('Email ou senha incorretos.');
    }
  };

  const handleLogout = () => {
    setIsAuthenticated(false);
    setData(prev => ({ ...prev, currentUser: null }));
    setLoginEmail('');
    setLoginPassword('');
    setActiveModule('electricity');
  };

  // --- User Management Handlers ---
  const handleEditUserClick = (user: User) => {
    setEditingUserId(user.id);
    setUserFormData({
      name: user.name,
      email: user.email,
      password: user.password || '',
      role: user.role,
      allowedUnits: user.allowedUnits || []
    });
  };

  const handleCancelUserEdit = () => {
    setEditingUserId(null);
    setUserFormData({ name: '', email: '', password: '', role: 'viewer', allowedUnits: [] });
  };

  const toggleUnitPermission = (unit: string) => {
    setUserFormData(prev => {
      const exists = prev.allowedUnits.includes(unit);
      return {
        ...prev,
        allowedUnits: exists 
          ? prev.allowedUnits.filter(u => u !== unit)
          : [...prev.allowedUnits, unit]
      };
    });
  };

  const handleSaveUser = () => {
    if (!userFormData.name || !userFormData.email || !userFormData.password) {
      alert("Por favor, preencha todos os campos.");
      return;
    }

    if (editingUserId) {
      // Update Existing
      setData(prev => ({
        ...prev,
        users: prev.users.map(u => u.id === editingUserId ? {
          ...u,
          name: userFormData.name,
          email: userFormData.email,
          password: userFormData.password,
          role: userFormData.role,
          allowedUnits: userFormData.allowedUnits
        } : u)
      }));
    } else {
      // Create New
      const userToAdd: User = {
        id: crypto.randomUUID(),
        name: userFormData.name,
        email: userFormData.email,
        password: userFormData.password,
        role: userFormData.role,
        allowedUnits: userFormData.allowedUnits
      };
      setData(prev => ({
        ...prev,
        users: [...prev.users, userToAdd]
      }));
    }
    handleCancelUserEdit();
  };

  const handleDeleteUser = (userId: string) => {
    if (userId === data.currentUser?.id) {
      alert("Você não pode excluir a si mesmo.");
      return;
    }
    if (window.confirm("Tem certeza que deseja remover este usuário?")) {
      setData(prev => ({
        ...prev,
        users: prev.users.filter(u => u.id !== userId)
      }));
    }
  };

  const handleImport = (type: keyof AppState, newData: any[]) => {
    setData(prev => ({
      ...prev,
      [type]: [...(prev[type] as any[]), ...newData]
    }));
  };

  // --- Unit Management ---
  const handleAddUnit = () => {
    if (newUnitName.trim() && !data.units.includes(newUnitName.trim())) {
      setData(prev => ({
        ...prev,
        units: [...prev.units, newUnitName.trim()]
      }));
      setNewUnitName('');
    }
  };

  const handleDeleteUnit = (unitName: string) => {
    if (window.confirm(`Tem certeza que deseja remover a unidade "${unitName}"? Isso não apagará os dados históricos, mas a removerá da lista de seleção.`)) {
      setData(prev => ({
        ...prev,
        units: prev.units.filter(u => u !== unitName)
      }));
    }
  };

  // --- Goal Updates ---
  const handleUpdateElectricityGoal = (newGoal: number) => {
    setData(prev => ({ ...prev, electricityGoal: newGoal }));
  };

  const handleUpdateWaterGoal = (newGoal: number) => {
    setData(prev => ({ ...prev, waterGoal: newGoal }));
  };

  const handleUpdateWasteGoal = (newGoal: number) => {
    setData(prev => ({ ...prev, wasteGoal: newGoal }));
  };

  // --- Data CRUD Handlers (Water, Elec, Waste) ---
  const handleUpdateWater = (updatedRecord: WaterRecord) => {
    setData(prev => ({
      ...prev,
      water: prev.water.map(r => r.id === updatedRecord.id ? updatedRecord : r)
    }));
  };

  const handleAddWater = (newRecord: WaterRecord) => {
    setData(prev => ({
      ...prev,
      water: [...prev.water, newRecord]
    }));
  };

  const handleDeleteWater = (id: string) => {
    setData(prev => ({
      ...prev,
      water: prev.water.filter(r => r.id !== id)
    }));
  };

  const handleUpdateElectricity = (updatedRecord: ElectricityRecord) => {
    setData(prev => ({
      ...prev,
      electricity: prev.electricity.map(r => r.id === updatedRecord.id ? updatedRecord : r)
    }));
  };

  const handleAddElectricity = (newRecord: ElectricityRecord) => {
    setData(prev => ({
      ...prev,
      electricity: [...prev.electricity, newRecord]
    }));
  };

  const handleDeleteElectricity = (id: string) => {
    setData(prev => ({
      ...prev,
      electricity: prev.electricity.filter(r => r.id !== id)
    }));
  };

  const handleUpdateWaste = (updatedRecord: WasteRecord) => {
    setData(prev => ({
      ...prev,
      waste: prev.waste.map(r => r.id === updatedRecord.id ? updatedRecord : r)
    }));
  };

  const handleAddWaste = (newRecord: WasteRecord) => {
    setData(prev => ({
      ...prev,
      waste: [...prev.waste, newRecord]
    }));
  };

  const handleDeleteWaste = (id: string) => {
    setData(prev => ({
      ...prev,
      waste: prev.waste.filter(r => r.id !== id)
    }));
  };

  // Effect to handle navigation restriction for viewers
  useEffect(() => {
    if (data.currentUser?.role === 'viewer' && (activeModule === 'import' || activeModule === 'settings')) {
       if (activeModule === 'import') setActiveModule('electricity');
    }
  }, [data.currentUser, activeModule]);

  // --- Access Control Filtering Logic ---
  const getVisibleData = () => {
    const user = data.currentUser;
    if (!user) return { units: [], electricity: [], water: [], waste: [] };

    // Managers see everything. Viewers see only allowed units.
    if (user.role === 'manager') {
      return {
        units: data.units,
        electricity: data.electricity,
        water: data.water,
        waste: data.waste // Waste usually doesn't have units in this specific model, or acts differently? 
        // Note: The waste type provided doesn't have a 'unit' field in type definition (only type, category).
        // If Waste is global, we return it all. If it needs unit filtering, the WasteRecord type needs 'unit'.
        // Looking at types.ts: WasteRecord does NOT have 'unit'. Assuming it's global or handled differently.
        // We will return full waste data for now as per schema.
      };
    }

    const allowed = user.allowedUnits || [];
    
    return {
      units: data.units.filter(u => allowed.includes(u)),
      electricity: data.electricity.filter(r => allowed.includes(r.unit)),
      water: data.water.filter(r => allowed.includes(r.unit)),
      waste: data.waste // Assuming waste is global/not unit specific based on current Type definition
    };
  };

  const filteredData = getVisibleData();


  // LOGIN SCREEN RENDER
  if (!isAuthenticated) {
    return (
      <div className={`min-h-screen flex items-center justify-center p-4 transition-colors duration-500 bg-[#f8fafc] dark:bg-slate-900`}>
        <div className="absolute top-6 right-6">
           <button 
              onClick={toggleTheme}
              className="p-2.5 rounded-full text-slate-500 hover:bg-slate-200 dark:text-slate-400 dark:hover:bg-slate-800 transition-colors"
           >
              {theme === 'light' ? <Moon size={20} /> : <Sun size={20} />}
           </button>
        </div>
        
        <div className="w-full max-w-md bg-white dark:bg-slate-800 rounded-3xl shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-700 animate-fade-in-up">
           <div className="p-10">
              <div className="flex justify-center mb-8">
                <div className="w-16 h-16 rounded-2xl bg-gradient-to-br from-ctdi-green to-emerald-600 flex items-center justify-center text-white shadow-lg shadow-emerald-200 dark:shadow-none">
                  <Leaf size={32} fill="currentColor" className="text-white/90" />
                </div>
              </div>
              <h1 className="text-3xl font-extrabold text-center text-slate-800 dark:text-white mb-2">CTDI EcoTrack</h1>
              <p className="text-center text-slate-500 dark:text-slate-400 mb-8">Faça login para acessar o sistema de gestão ambiental.</p>

              <form onSubmit={handleLogin} className="space-y-5">
                <div>
                  <label className="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Email Corporativo</label>
                  <div className="relative">
                    <Mail className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" size={18} />
                    <input 
                      type="email" 
                      value={loginEmail}
                      onChange={(e) => setLoginEmail(e.target.value)}
                      className="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-ctdi-blue focus:border-transparent transition-all"
                      placeholder="ex: admin@ctdi.com"
                      required
                    />
                  </div>
                </div>
                <div>
                  <label className="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Senha</label>
                  <div className="relative">
                    <Lock className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" size={18} />
                    <input 
                      type={showPassword ? 'text' : 'password'}
                      value={loginPassword}
                      onChange={(e) => setLoginPassword(e.target.value)}
                      className="w-full pl-10 pr-10 py-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-ctdi-blue focus:border-transparent transition-all"
                      placeholder="••••••••"
                      required
                    />
                    <button
                      type="button"
                      onClick={() => setShowPassword(!showPassword)}
                      className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-ctdi-blue p-1 rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors"
                      tabIndex={-1}
                    >
                       {showPassword ? <EyeOff size={18} /> : <Eye size={18} />}
                    </button>
                  </div>
                </div>

                {loginError && (
                  <div className="p-3 rounded-lg bg-rose-50 dark:bg-rose-900/20 text-rose-600 dark:text-rose-400 text-sm font-medium text-center">
                    {loginError}
                  </div>
                )}

                <button 
                  type="submit"
                  className="w-full py-3.5 bg-gradient-to-r from-ctdi-blue to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-bold rounded-xl shadow-lg shadow-blue-200 dark:shadow-none transition-all flex items-center justify-center gap-2"
                >
                  <LogIn size={20} /> Entrar
                </button>
              </form>
           </div>
           <div className="px-10 py-4 bg-slate-50 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-700 text-center">
              <p className="text-xs text-slate-400">
                Acesso restrito a colaboradores autorizados.
                <br/>Admin Padrão: admin@ctdi.com / admin
              </p>
           </div>
        </div>
      </div>
    );
  }

  // APP CONTENT RENDER
  const renderContent = () => {
    switch (activeModule) {
      case 'electricity':
        return (
          <ElectricityDashboard 
            data={filteredData.electricity} 
            units={filteredData.units}
            goal={data.electricityGoal}
            onUpdate={handleUpdateElectricity}
            onAdd={handleAddElectricity}
            onDelete={handleDeleteElectricity}
            userRole={data.currentUser?.role || 'viewer'}
            theme={theme} 
          />
        );
      case 'water':
        return (
          <WaterDashboard 
            data={filteredData.water} 
            units={filteredData.units}
            goal={data.waterGoal}
            onUpdate={handleUpdateWater}
            onAdd={handleAddWater}
            onDelete={handleDeleteWater}
            userRole={data.currentUser?.role || 'viewer'}
            theme={theme}
          />
        );
      case 'waste':
        return (
          <WasteDashboard 
            data={filteredData.waste}
            goal={data.wasteGoal}
            onUpdate={handleUpdateWaste}
            onAdd={handleAddWaste}
            onDelete={handleDeleteWaste}
            userRole={data.currentUser?.role || 'viewer'}
            theme={theme} 
          />
        );
      case 'import':
        return data.currentUser?.role === 'manager' 
          ? <DataImport onImport={handleImport} />
          : <div className="text-center p-10 text-slate-500 dark:text-slate-400">Acesso Negado</div>;
      case 'settings':
        return (
          <div className="p-2 md:p-6 max-w-6xl mx-auto animate-fade-in">
             <div className="flex items-center justify-between mb-8">
                <h2 className="text-2xl font-bold text-slate-800 dark:text-white">Configurações</h2>
             </div>

             {/* Settings Navigation Tabs */}
             <div className="flex gap-2 mb-8 overflow-x-auto pb-2">
                {[
                  { id: 'goals', label: 'Metas e KPIs', icon: Target },
                  { id: 'users', label: 'Gestão de Usuários', icon: Users },
                  { id: 'units', label: 'Unidades', icon: Building2 },
                  { id: 'system', label: 'Sistema', icon: Info },
                ].map(tab => (
                   <button
                     key={tab.id}
                     onClick={() => setActiveSettingsTab(tab.id as any)}
                     className={`flex items-center gap-2 px-6 py-3 rounded-full text-sm font-bold transition-all whitespace-nowrap
                       ${activeSettingsTab === tab.id 
                         ? 'bg-slate-800 dark:bg-white text-white dark:text-slate-900 shadow-md' 
                         : 'bg-white dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-700'
                       }
                     `}
                   >
                     <tab.icon size={18} />
                     {tab.label}
                   </button>
                ))}
             </div>
             
             {/* --- GOALS TAB --- */}
             {activeSettingsTab === 'goals' && (
                <div className="bg-white dark:bg-slate-800 p-8 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 animate-fade-in">
                  <div className="mb-6">
                    <h3 className="text-lg font-bold text-slate-800 dark:text-white">Definição de Metas</h3>
                    <p className="text-sm text-slate-500 dark:text-slate-400">Ajuste os objetivos estratégicos. Alterações refletem imediatamente nos dashboards.</p>
                  </div>

                  <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12">
                      {/* Electricity Goal */}
                      <div>
                        <div className="flex items-center gap-2 mb-2">
                           <label className="block text-sm font-semibold text-slate-700 dark:text-slate-300">Energia Limpa (% Alvo)</label>
                           <div className="group relative">
                              <Info size={16} className="text-slate-400 cursor-help" />
                              <div className="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-56 p-2.5 bg-slate-900 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10 shadow-xl">
                                Fórmula: (Energia Renovável / Energia Total) * 100.
                                <br/><br/>
                                Define a porcentagem mínima esperada de consumo proveniente de fontes limpas (Mercado Livre/Flora).
                              </div>
                           </div>
                        </div>
                        <div className="flex items-center gap-4">
                            <input 
                              type="range" 
                              min="0" 
                              max="100" 
                              value={data.electricityGoal}
                              onChange={(e) => handleUpdateElectricityGoal(Number(e.target.value))}
                              disabled={data.currentUser?.role !== 'manager'}
                              className="w-full h-2 bg-slate-200 dark:bg-slate-700 rounded-lg appearance-none cursor-pointer accent-purple-600"
                            />
                            <div className="relative w-24">
                              <input 
                                type="number"
                                min="0"
                                max="100"
                                value={data.electricityGoal}
                                onChange={(e) => handleUpdateElectricityGoal(Number(e.target.value))}
                                disabled={data.currentUser?.role !== 'manager'}
                                className="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-center font-bold text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-ctdi-blue"
                              />
                              <span className="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400 pointer-events-none">%</span>
                            </div>
                        </div>
                      </div>

                      {/* Waste Recycling Goal */}
                      <div>
                        <div className="flex items-center gap-2 mb-2">
                           <label className="block text-sm font-semibold text-slate-700 dark:text-slate-300">Meta de Reciclagem (%)</label>
                           <div className="group relative">
                              <Info size={16} className="text-slate-400 cursor-help" />
                              <div className="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-56 p-2.5 bg-slate-900 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10 shadow-xl">
                                Fórmula: (Peso Reciclável / Peso Total) * 100.
                                <br/><br/>
                                Representa a eficiência da operação em desviar resíduos de aterros sanitários.
                              </div>
                           </div>
                        </div>
                        <div className="flex items-center gap-4">
                            <input 
                              type="range" 
                              min="0" 
                              max="100" 
                              value={data.wasteGoal}
                              onChange={(e) => handleUpdateWasteGoal(Number(e.target.value))}
                              disabled={data.currentUser?.role !== 'manager'}
                              className="w-full h-2 bg-slate-200 dark:bg-slate-700 rounded-lg appearance-none cursor-pointer accent-green-600"
                            />
                            <div className="relative w-24">
                              <input 
                                type="number"
                                min="0"
                                max="100"
                                value={data.wasteGoal}
                                onChange={(e) => handleUpdateWasteGoal(Number(e.target.value))}
                                disabled={data.currentUser?.role !== 'manager'}
                                className="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-center font-bold text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-ctdi-blue"
                              />
                              <span className="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400 pointer-events-none">%</span>
                            </div>
                        </div>
                      </div>

                      {/* Water Goal */}
                      <div>
                        <div className="flex items-center gap-2 mb-2">
                           <label className="block text-sm font-semibold text-slate-700 dark:text-slate-300">Limite Água (m³ / unidade)</label>
                           <div className="group relative">
                              <Info size={16} className="text-slate-400 cursor-help" />
                              <div className="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-64 p-2.5 bg-slate-900 text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10 shadow-xl">
                                Cálculo: Média de consumo mensal por unidade vs Limite.
                                <br/><br/>
                                Se "Todas as Unidades" estiver selecionado, o limite visualizado é (Meta Base * Nº Unidades). Se uma unidade for selecionada, é o valor fixo abaixo.
                              </div>
                           </div>
                        </div>
                        <div className="flex items-center gap-4">
                            <input 
                              type="range" 
                              min="0" 
                              max="100" 
                              step="1"
                              value={data.waterGoal}
                              onChange={(e) => handleUpdateWaterGoal(Number(e.target.value))}
                              disabled={data.currentUser?.role !== 'manager'}
                              className="w-full h-2 bg-slate-200 dark:bg-slate-700 rounded-lg appearance-none cursor-pointer accent-blue-500"
                            />
                            <div className="relative w-24">
                              <input 
                                type="number"
                                min="0"
                                max="1000"
                                value={data.waterGoal}
                                onChange={(e) => handleUpdateWaterGoal(Number(e.target.value))}
                                disabled={data.currentUser?.role !== 'manager'}
                                className="w-full px-3 py-2 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-lg text-center font-bold text-slate-800 dark:text-white outline-none focus:ring-2 focus:ring-ctdi-blue"
                              />
                              <span className="absolute right-2 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400 pointer-events-none">m³</span>
                            </div>
                        </div>
                      </div>
                  </div>
                </div>
             )}
             
             {/* --- USERS TAB --- */}
             {activeSettingsTab === 'users' && (
                <div className="bg-white dark:bg-slate-800 p-8 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 animate-fade-in">
                 {data.currentUser?.role === 'manager' ? (
                   <>
                     <div className="mb-6 flex flex-col md:flex-row justify-between md:items-center gap-4">
                       <div>
                          <h3 className="text-lg font-bold text-slate-800 dark:text-white">Usuários do Sistema</h3>
                          <p className="text-sm text-slate-500 dark:text-slate-400">Controle quem tem acesso e seus privilégios.</p>
                       </div>
                     </div>

                     <div className="bg-slate-50 dark:bg-slate-900/50 p-6 rounded-xl border border-slate-200 dark:border-slate-700 mb-8">
                       <h4 className="text-sm font-bold text-slate-700 dark:text-slate-300 mb-4 uppercase tracking-wider">
                         {editingUserId ? 'Editar Usuário' : 'Novo Usuário'}
                       </h4>
                       <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                          <input 
                            type="text" 
                            placeholder="Nome Completo" 
                            value={userFormData.name} 
                            onChange={e => setUserFormData({...userFormData, name: e.target.value})} 
                            className="px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-600 dark:bg-slate-800 text-slate-900 dark:text-white text-sm outline-none focus:ring-2 focus:ring-ctdi-blue" 
                          />
                          <input 
                            type="email" 
                            placeholder="Email" 
                            value={userFormData.email} 
                            onChange={e => setUserFormData({...userFormData, email: e.target.value})} 
                            className="px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-600 dark:bg-slate-800 text-slate-900 dark:text-white text-sm outline-none focus:ring-2 focus:ring-ctdi-blue" 
                          />
                          <input 
                            type="text" 
                            placeholder="Senha" 
                            value={userFormData.password} 
                            onChange={e => setUserFormData({...userFormData, password: e.target.value})} 
                            className="px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-600 dark:bg-slate-800 text-slate-900 dark:text-white text-sm outline-none focus:ring-2 focus:ring-ctdi-blue" 
                          />
                          <select 
                            value={userFormData.role} 
                            onChange={e => setUserFormData({...userFormData, role: e.target.value as UserRole})} 
                            className="px-4 py-2 rounded-lg border border-slate-200 dark:border-slate-600 dark:bg-slate-800 text-slate-900 dark:text-white text-sm outline-none focus:ring-2 focus:ring-ctdi-blue"
                          >
                            <option value="viewer">Visualizador</option>
                            <option value="manager">Gestor</option>
                          </select>
                       </div>

                       {/* Unit Access Control */}
                       <div className="mb-4">
                           <label className="block text-xs font-bold text-slate-500 dark:text-slate-400 mb-2 uppercase tracking-wide">
                             Acesso às Unidades (Permissões)
                           </label>
                           <div className="flex flex-wrap gap-2">
                              {data.units.map(unit => {
                                const isSelected = userFormData.allowedUnits.includes(unit);
                                return (
                                  <button
                                    key={unit}
                                    onClick={() => toggleUnitPermission(unit)}
                                    className={`flex items-center gap-2 px-3 py-1.5 rounded-lg border text-sm transition-all
                                      ${isSelected 
                                        ? 'bg-blue-50 dark:bg-blue-900/30 border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300' 
                                        : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-600 text-slate-500 dark:text-slate-400 hover:border-slate-300'
                                      }
                                    `}
                                  >
                                    {isSelected ? <CheckSquare size={16} className="text-blue-500" /> : <Square size={16} />}
                                    {unit}
                                  </button>
                                );
                              })}
                           </div>
                           <p className="text-xs text-slate-400 mt-2">
                             * Gestores têm acesso total automático. Seleção aplica-se principalmente a Visualizadores.
                           </p>
                       </div>

                       <div className="flex gap-2">
                             {editingUserId && (
                               <button onClick={handleCancelUserEdit} className="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-sm font-bold transition-colors">Cancelar</button>
                             )}
                             <button onClick={handleSaveUser} className="px-6 py-2 bg-ctdi-blue text-white rounded-lg text-sm font-bold hover:bg-blue-700 transition-colors shadow-lg shadow-blue-200 dark:shadow-none">
                               {editingUserId ? 'Atualizar Usuário' : 'Adicionar Usuário'}
                             </button>
                       </div>
                     </div>

                     <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {data.users.map(u => (
                          <div key={u.id} className="flex flex-col p-4 rounded-xl border border-slate-100 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm group">
                             <div className="flex items-center justify-between mb-3">
                               <div className="flex items-center gap-4">
                                 <div className={`w-10 h-10 rounded-full flex items-center justify-center text-white text-sm font-bold ${u.role === 'manager' ? 'bg-purple-500' : 'bg-slate-400'}`}>
                                   {u.name.charAt(0)}
                                 </div>
                                 <div>
                                   <p className="text-sm font-bold text-slate-800 dark:text-white">{u.name}</p>
                                   <p className="text-xs text-slate-500">{u.email}</p>
                                 </div>
                               </div>
                               <div className="flex items-center gap-3">
                                  <span className={`text-[10px] font-bold uppercase px-2 py-1 rounded ${u.role === 'manager' ? 'bg-purple-100 text-purple-700' : 'bg-slate-100 text-slate-600'}`}>
                                    {u.role === 'manager' ? 'Gestor' : 'Viewer'}
                                  </span>
                                  <div className="flex gap-1 opacity-100 md:opacity-0 group-hover:opacity-100 transition-opacity">
                                      <button onClick={() => handleEditUserClick(u)} className="p-2 text-blue-500 hover:bg-blue-50 rounded-lg transition-colors"><Edit2 size={16} /></button>
                                      <button onClick={() => handleDeleteUser(u.id)} className="p-2 text-rose-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors"><Trash2 size={16} /></button>
                                  </div>
                               </div>
                             </div>
                             {/* Allowed Units Tags */}
                             <div className="flex flex-wrap gap-1 mt-auto">
                               {u.role === 'manager' ? (
                                 <span className="text-[10px] px-2 py-0.5 bg-slate-100 dark:bg-slate-700 text-slate-500 rounded-full border border-slate-200 dark:border-slate-600">Acesso Total</span>
                               ) : (
                                 u.allowedUnits && u.allowedUnits.length > 0 ? (
                                    u.allowedUnits.map(unit => (
                                      <span key={unit} className="text-[10px] px-2 py-0.5 bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-300 rounded-full border border-blue-100 dark:border-blue-800">
                                        {unit}
                                      </span>
                                    ))
                                 ) : (
                                   <span className="text-[10px] px-2 py-0.5 bg-rose-50 dark:bg-rose-900/20 text-rose-500 rounded-full border border-rose-100 dark:border-rose-800">Sem Acesso</span>
                                 )
                               )}
                             </div>
                          </div>
                        ))}
                     </div>
                   </>
                 ) : (
                    <div className="text-center py-10">
                        <Lock className="mx-auto text-slate-300 mb-4" size={48} />
                        <h3 className="text-lg font-bold text-slate-700 dark:text-slate-300">Acesso Restrito</h3>
                        <p className="text-slate-500">Apenas gestores podem gerenciar usuários.</p>
                    </div>
                 )}
                </div>
             )}

             {/* --- UNITS TAB --- */}
             {activeSettingsTab === 'units' && (
                <div className="bg-white dark:bg-slate-800 p-8 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 animate-fade-in">
                  <div className="mb-6">
                    <h3 className="text-lg font-bold text-slate-800 dark:text-white">Gerenciamento de Unidades</h3>
                    <p className="text-sm text-slate-500 dark:text-slate-400">Adicione ou remova galpões/unidades disponíveis no sistema.</p>
                  </div>

                  {data.currentUser?.role === 'manager' && (
                    <div className="flex gap-3 mb-6">
                      <input 
                          type="text" 
                          value={newUnitName}
                          onChange={(e) => setNewUnitName(e.target.value)}
                          placeholder="Nome da nova unidade (ex: Galpão 50)"
                          className="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white outline-none focus:ring-2 focus:ring-ctdi-blue transition-all"
                      />
                      <button 
                          onClick={handleAddUnit}
                          disabled={!newUnitName.trim()}
                          className="flex items-center gap-2 px-6 py-2.5 bg-ctdi-blue hover:bg-blue-700 disabled:bg-slate-300 dark:disabled:bg-slate-700 text-white rounded-xl font-semibold transition-all shadow-md shadow-blue-200 dark:shadow-none"
                      >
                        <Plus size={18} /> Adicionar
                      </button>
                    </div>
                  )}

                  <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                      {data.units.map(unit => (
                        <div key={unit} className="flex items-center justify-between p-4 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 group">
                          <span className="font-medium text-slate-700 dark:text-slate-300">{unit}</span>
                          {data.currentUser?.role === 'manager' && (
                            <button 
                              onClick={() => handleDeleteUnit(unit)}
                              className="p-1.5 text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/20 rounded-lg transition-colors opacity-0 group-hover:opacity-100"
                              title="Remover Unidade"
                            >
                              <Trash2 size={16} />
                            </button>
                          )}
                        </div>
                      ))}
                  </div>
                </div>
             )}

             {/* --- SYSTEM TAB --- */}
             {activeSettingsTab === 'system' && (
                <div className="bg-white dark:bg-slate-800 p-8 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 animate-fade-in">
                  <h3 className="text-lg font-bold text-slate-800 dark:text-white mb-6">Informações do Sistema</h3>
                  <div className="grid grid-cols-2 gap-4 text-sm">
                    <div className="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-700">
                        <p className="text-slate-500 dark:text-slate-400 mb-1">Status da API</p>
                        <span className="text-emerald-600 dark:text-emerald-400 font-bold flex items-center gap-2">● Online</span>
                    </div>
                    <div className="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-700">
                        <p className="text-slate-500 dark:text-slate-400 mb-1">Versão</p>
                        <span className="text-slate-700 dark:text-slate-300 font-bold">1.0.9 (Stable)</span>
                    </div>
                    <div className="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-700">
                        <p className="text-slate-500 dark:text-slate-400 mb-1">Tema Atual</p>
                        <span className="text-slate-700 dark:text-slate-300 font-bold capitalize">{theme}</span>
                    </div>
                    <div className="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-700">
                        <p className="text-slate-500 dark:text-slate-400 mb-1">Usuário Logado</p>
                        <div className="flex flex-col">
                           <span className="text-slate-700 dark:text-slate-300 font-bold">{data.currentUser?.name}</span>
                           <span className="text-xs text-slate-400 capitalize">{data.currentUser?.role === 'manager' ? 'Gestor' : 'Visualizador'}</span>
                        </div>
                    </div>
                  </div>
                </div>
             )}
          </div>
        );
      default:
        return <div>Módulo não encontrado</div>;
    }
  };

  return (
    <Layout 
      activeModule={activeModule} 
      setActiveModule={setActiveModule}
      currentUser={data.currentUser}
      onLogout={handleLogout}
      theme={theme}
      toggleTheme={toggleTheme}
    >
      {renderContent()}
    </Layout>
  );
};

export default App;
