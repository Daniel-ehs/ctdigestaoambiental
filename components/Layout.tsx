
import React from 'react';
import { 
  Zap, 
  Droplets, 
  Trash2, 
  Upload, 
  Settings, 
  Menu, 
  ChevronRight,
  Sun,
  Moon,
  Leaf,
  LogOut
} from 'lucide-react';
import { ModuleType, User } from '../types';

interface LayoutProps {
  children: React.ReactNode;
  activeModule: ModuleType;
  setActiveModule: (m: ModuleType) => void;
  currentUser: User | null;
  onLogout: () => void;
  theme: 'light' | 'dark';
  toggleTheme: () => void;
}

const Layout: React.FC<LayoutProps> = ({ 
  children, 
  activeModule, 
  setActiveModule,
  currentUser,
  onLogout,
  theme,
  toggleTheme
}) => {
  const [sidebarOpen, setSidebarOpen] = React.useState(true);

  const LogoIcon = () => (
    <div className="w-8 h-8 rounded-lg bg-gradient-to-br from-ctdi-green to-emerald-600 flex items-center justify-center text-white shadow-lg shadow-emerald-200 dark:shadow-none shrink-0">
      <Leaf size={18} fill="currentColor" className="text-white/90" />
    </div>
  );

  const NavItem = ({ module, icon: Icon, label }: { module: ModuleType, icon: any, label: string }) => {
    const isActive = activeModule === module;
    return (
      <button
        onClick={() => setActiveModule(module)}
        title={!sidebarOpen ? label : ''}
        className={`group relative w-full flex items-center ${sidebarOpen ? 'px-4 gap-3' : 'px-0 justify-center'} py-3.5 rounded-xl transition-all duration-300 ease-out
          ${isActive 
            ? 'bg-gradient-to-r from-ctdi-blue to-blue-600 text-white shadow-lg shadow-blue-500/20' 
            : 'text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-ctdi-blue dark:hover:text-blue-400'
          }`}
      >
        <Icon size={22} className={`shrink-0 transition-transform duration-300 ${isActive ? 'scale-110' : 'group-hover:scale-110'}`} />
        {sidebarOpen && (
          <span className={`font-medium tracking-wide whitespace-nowrap overflow-hidden transition-opacity duration-300 ${isActive ? 'opacity-100' : 'opacity-90'}`}>
            {label}
          </span>
        )}
        {sidebarOpen && isActive && (
          <ChevronRight size={16} className="absolute right-3 opacity-60" />
        )}
      </button>
    );
  };

  return (
    <div className={`flex h-screen bg-[#f8fafc] dark:bg-slate-900 overflow-hidden font-sans text-slate-900 dark:text-slate-100 selection:bg-blue-100 dark:selection:bg-blue-900`}>
      {/* Sidebar with Glassmorphism feel */}
      <aside 
        className={`${
          sidebarOpen ? 'w-72' : 'w-24'
        } bg-white dark:bg-slate-900 h-full shadow-2xl shadow-slate-200/50 dark:shadow-black/20 z-30 transition-all duration-500 cubic-bezier(0.4, 0, 0.2, 1) flex flex-col border-r border-slate-100 dark:border-slate-800`}
      >
        <div className="h-24 flex items-center justify-between px-6 mb-2">
          {sidebarOpen ? (
            <div className="flex items-center gap-3">
               <LogoIcon />
               <div className="flex flex-col">
                  <div className="flex items-center gap-1 font-extrabold text-2xl tracking-tight text-slate-800 dark:text-white">
                    <span className="text-ctdi-blue">CTDI</span>
                    <span className="text-slate-800 dark:text-slate-200">Eco</span>
                  </div>
                  <span className="text-[10px] font-semibold text-slate-400 uppercase tracking-[0.2em] -mt-1">Management</span>
               </div>
            </div>
          ) : (
            <div className="w-full flex justify-center">
               <LogoIcon />
            </div>
          )}
          {sidebarOpen && (
            <button 
              onClick={() => setSidebarOpen(false)} 
              className="p-2 rounded-lg text-slate-400 hover:text-ctdi-blue hover:bg-blue-50 dark:hover:bg-slate-800 transition-colors"
            >
                <Menu size={20} />
            </button>
          )}
        </div>
        
        {!sidebarOpen && (
             <button onClick={() => setSidebarOpen(true)} className="w-full flex justify-center py-6 text-slate-400 hover:text-ctdi-blue transition-colors">
                <Menu size={24} />
            </button>
        )}

        <nav className="flex-1 px-4 space-y-3 py-4">
          <div className={`text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-4 ${sidebarOpen ? 'px-4' : 'text-center'}`}>
            {sidebarOpen ? 'Módulos Operacionais' : 'OPS'}
          </div>
          <NavItem module="electricity" icon={Zap} label="Energia" />
          <NavItem module="water" icon={Droplets} label="Água" />
          <NavItem module="waste" icon={Trash2} label="Resíduos" />
          
          <div className="my-8 border-t border-slate-100 dark:border-slate-800 mx-4 opacity-50"></div>
          
          <div className={`text-[11px] font-bold text-slate-400 uppercase tracking-widest mb-4 ${sidebarOpen ? 'px-4' : 'text-center'}`}>
            {sidebarOpen ? 'Sistema' : 'SYS'}
          </div>
          {currentUser?.role === 'manager' && (
             <NavItem module="import" icon={Upload} label="Importar Dados" />
          )}
          <NavItem module="settings" icon={Settings} label="Ajustes" />
        </nav>

        <div className="p-6 mt-auto">
           <div className={`
             flex items-center gap-4 p-4 rounded-2xl transition-all duration-300
             ${sidebarOpen ? 'bg-gradient-to-br from-slate-50 to-white dark:from-slate-800 dark:to-slate-800 border border-slate-100 dark:border-slate-700 shadow-lg shadow-slate-100 dark:shadow-none' : 'justify-center bg-transparent'}
           `}>
             <div className={`
               w-10 h-10 rounded-xl bg-gradient-to-br from-ctdi-green to-emerald-500 
               flex items-center justify-center text-white font-bold text-sm shadow-md shadow-emerald-200 dark:shadow-none shrink-0
             `}>
               {currentUser?.name.charAt(0)}
             </div>
             {sidebarOpen && (
               <div className="flex-1 overflow-hidden">
                 <p className="text-sm font-bold text-slate-700 dark:text-slate-200 truncate">{currentUser?.name}</p>
                 <p className="text-xs text-slate-400 dark:text-slate-500 truncate">{currentUser?.email}</p>
               </div>
             )}
           </div>
        </div>
      </aside>

      {/* Main Content Area */}
      <main className="flex-1 flex flex-col h-full overflow-hidden relative bg-[#f8fafc] dark:bg-slate-900 transition-colors duration-300">
        {/* Modern Header with subtle blur */}
        <header className="h-20 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-200/60 dark:border-slate-800 flex items-center justify-between px-10 sticky top-0 z-20">
           <div className="flex flex-col">
             <h1 className="text-2xl font-bold text-slate-800 dark:text-white tracking-tight flex items-center gap-3">
               {activeModule === 'electricity' && 'Energia Elétrica'}
               {activeModule === 'water' && 'Recursos Hídricos'}
               {activeModule === 'waste' && 'Gestão de Resíduos'}
               {activeModule === 'import' && 'Central de Importação'}
               {activeModule === 'settings' && 'Configurações'}
             </h1>
             <span className="text-xs font-medium text-slate-400 tracking-wide mt-0.5">Visão Geral do Sistema</span>
           </div>
           
           <div className="flex items-center gap-6">
              <button 
                onClick={toggleTheme}
                className="p-2.5 rounded-full text-slate-500 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800 transition-colors"
                title={theme === 'light' ? 'Modo Escuro' : 'Modo Claro'}
              >
                {theme === 'light' ? <Moon size={20} /> : <Sun size={20} />}
              </button>
              
              <div className="h-6 w-px bg-slate-200 dark:bg-slate-700 hidden md:block"></div>

              <div className="hidden md:flex flex-col items-end">
                <span className="text-xs font-semibold text-slate-400 uppercase tracking-wider">Última Sincronização</span>
                <span className="text-sm font-medium text-slate-600 dark:text-slate-300 font-mono">{new Date().toLocaleDateString('pt-BR')} • Online</span>
              </div>
              <button 
                onClick={onLogout}
                className="h-10 w-10 rounded-full bg-rose-50 hover:bg-rose-100 dark:bg-rose-900/20 dark:hover:bg-rose-900/40 flex items-center justify-center text-rose-500 dark:text-rose-400 transition-colors"
                title="Sair do Sistema"
              >
                <LogOut size={20} />
              </button>
           </div>
        </header>

        <div className="flex-1 overflow-y-auto p-8 scroll-smooth">
          <div className="max-w-[1600px] mx-auto animate-fade-in-up">
            {children}
          </div>
        </div>
      </main>
    </div>
  );
};

export default Layout;
