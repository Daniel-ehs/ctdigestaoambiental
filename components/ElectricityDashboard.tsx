

import React, { useMemo, useState, useRef } from 'react';
import {
  BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer, ReferenceLine, Cell, AreaChart, Area
} from 'recharts';
import { Zap, Leaf, DollarSign, Activity, Sparkles, Plus, Edit2, Trash2, X, Table, Save, Search, Filter, Coins, Camera, Download, Loader2 } from 'lucide-react';
import { ElectricityRecord, UserRole } from '../types';
import { MetricCard } from './Metrics';
import { generateInsights } from '../services/geminiService';
import html2canvas from 'html2canvas';

interface Props {
  data: ElectricityRecord[];
  units: string[];
  goal: number; // Dynamic goal percentage
  onUpdate: (record: ElectricityRecord) => void;
  onAdd: (record: ElectricityRecord) => void;
  onDelete: (id: string) => void;
  userRole: UserRole;
  theme: 'light' | 'dark';
}

export const ElectricityDashboard: React.FC<Props> = ({ data, units, goal, onUpdate, onAdd, onDelete, userRole, theme }) => {
  const currentYear = new Date().getFullYear().toString();
  const [selectedUnit, setSelectedUnit] = useState<string | 'All'>('All');
  const [selectedYear, setSelectedYear] = useState<string>(currentYear);
  const [insights, setInsights] = useState<string | null>(null);
  const [loadingInsights, setLoadingInsights] = useState(false);
  const [isExporting, setIsExporting] = useState(false);

  // Refs for chart exporting
  const dashboardRef = useRef<HTMLDivElement>(null);
  const chartRef1 = useRef<HTMLDivElement>(null);
  const chartRef2 = useRef<HTMLDivElement>(null);
  const chartRef3 = useRef<HTMLDivElement>(null);

  const GOAL_PERCENTAGE = goal;

  // Modal State
  const [isManageModalOpen, setIsManageModalOpen] = useState(false);
  const [editingId, setEditingId] = useState<string | null>(null);
  const [formData, setFormData] = useState<Partial<ElectricityRecord>>({});

  // Modal Filter State
  const [modalSearchTerm, setModalSearchTerm] = useState('');
  const [modalFilterUnit, setModalFilterUnit] = useState<string | 'All'>('All');

  // Calculate available years from data + current year
  const availableYears = useMemo(() => {
    const years = new Set<string>();
    years.add(currentYear);
    data.forEach(d => years.add(d.date.substring(0, 4)));
    const sortedYears = Array.from(years).sort().reverse();
    console.log('Electricity availableYears:', sortedYears, 'Selected:', currentYear);
    return sortedYears;
  }, [data, currentYear]);

  const filteredData = useMemo(() => {
    return data.filter(d => {
      const matchUnit = selectedUnit === 'All' || d.unit === selectedUnit;
      const matchYear = d.date.startsWith(selectedYear);
      return matchUnit && matchYear;
    }).sort((a, b) => new Date(a.date).getTime() - new Date(b.date).getTime());
  }, [data, selectedUnit, selectedYear]);

  // Calculations
  const stats = useMemo(() => {
    const totalKwh = filteredData.reduce((acc, curr) => acc + curr.cpflKwh + curr.floraKwh, 0);
    const totalCost = filteredData.reduce((acc, curr) => acc + curr.cpflCost + curr.floraCost, 0);
    const totalSavings = filteredData.reduce((acc, curr) => acc + curr.floraSavings, 0);

    // Calculate Average Monthly Renewable Percentage (Requested: Average of monthly percentages)
    const months = Array.from(new Set(filteredData.map(d => d.date.substring(0, 7))));
    let sumPercentages = 0;
    let countMonths = 0;

    months.forEach(month => {
      const monthRecords = filteredData.filter(d => d.date.startsWith(month));
      const monthTotal = monthRecords.reduce((acc, curr) => acc + curr.cpflKwh + curr.floraKwh, 0);
      const monthFlora = monthRecords.reduce((acc, curr) => acc + curr.floraKwh, 0);

      if (monthTotal > 0) {
        sumPercentages += (monthFlora / monthTotal) * 100;
        countMonths++;
      }
    });

    const renewablePercentage = countMonths > 0 ? sumPercentages / countMonths : 0;

    return { totalKwh, totalCost, totalSavings, renewablePercentage };
  }, [filteredData]);

  // Chart Data Preparation
  const chartData = useMemo(() => {
    const months = Array.from(new Set(filteredData.map(d => d.date.substring(0, 7)))).sort();
    return months.map(month => {
      const records = filteredData.filter(d => d.date.startsWith(month));
      const cpflKwh = records.reduce((acc, curr) => acc + curr.cpflKwh, 0);
      const floraKwh = records.reduce((acc, curr) => acc + curr.floraKwh, 0);
      const dateObj = new Date(month + '-01T12:00:00'); // Fix TZ issues

      return {
        month: dateObj.toLocaleString('pt-BR', { month: 'short' }).toUpperCase(),
        fullDate: dateObj.toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' }),
        'CPFL (Convencional)': cpflKwh,
        'Flora (Renovável)': floraKwh,
        total: cpflKwh + floraKwh
      };
    });
  }, [filteredData]);

  const savingsData = useMemo(() => {
    const months = Array.from(new Set(filteredData.map(d => d.date.substring(0, 7)))).sort();
    return months.map(month => {
      const records = filteredData.filter(d => d.date.startsWith(month));
      const savings = records.reduce((acc, curr) => acc + curr.floraSavings, 0);
      const dateObj = new Date(month + '-01T12:00:00');
      return {
        month: dateObj.toLocaleString('pt-BR', { month: 'short' }).toUpperCase(),
        savings
      }
    });
  }, [filteredData]);

  // Chart Colors
  const chartColors = {
    grid: theme === 'dark' ? '#334155' : '#f1f5f9',
    text: theme === 'dark' ? '#94a3b8' : '#64748b',
    cpfl: '#94a3b8', // Slate 400
    flora: '#78be20', // CTDI Green
  };

  const handleAnalyze = async () => {
    setLoadingInsights(true);
    const context = JSON.stringify(chartData);
    const result = await generateInsights(context, `Energia Elétrica para ${selectedUnit === 'All' ? 'Todas as Unidades' : selectedUnit}`);
    setInsights(result);
    setLoadingInsights(false);
  };

  const handleDownloadChart = async (ref: React.RefObject<HTMLDivElement>, fileName: string) => {
    if (ref.current) {
      try {
        const canvas = await html2canvas(ref.current, {
          backgroundColor: theme === 'dark' ? '#1e293b' : '#ffffff',
          scale: 2 // High resolution
        });
        const link = document.createElement('a');
        link.download = `${fileName}.png`;
        link.href = canvas.toDataURL();
        link.click();
      } catch (error) {
        console.error("Erro ao baixar gráfico:", error);
        alert("Erro ao baixar gráfico.");
      }
    }
  };

  const handleExportDashboard = async () => {
    if (dashboardRef.current) {
      setIsExporting(true);
      try {
        // Wait for UI to settle
        await new Promise(resolve => setTimeout(resolve, 100));

        const canvas = await html2canvas(dashboardRef.current, {
          scale: 2,
          backgroundColor: theme === 'dark' ? '#0f172a' : '#f8fafc',
          ignoreElements: (element) => element.classList.contains('no-export'), // Hide buttons
          useCORS: true
        });
        const link = document.createElement('a');
        link.download = `Relatorio_Energia_${selectedUnit}_${selectedYear}.png`;
        link.href = canvas.toDataURL('image/png');
        link.click();
      } catch (error) {
        console.error("Export error:", error);
        alert("Erro ao exportar dashboard.");
      } finally {
        setIsExporting(false);
      }
    }
  }

  // --- CRUD Operations ---
  const handleEditClick = (record: ElectricityRecord) => {
    setEditingId(record.id);
    setFormData(record);
  };

  const handleAddNewClick = () => {
    setEditingId('NEW');
    setFormData({
      date: new Date().toISOString().split('T')[0],
      unit: units[0] || 'Galpão 6',
      cpflKwh: 0,
      cpflCost: 0,
      floraKwh: 0,
      floraCost: 0,
      floraSavings: 0
    });
  };

  const handleSave = () => {
    if (!formData.date || !formData.unit) return;

    const recordData: ElectricityRecord = {
      id: editingId === 'NEW' ? crypto.randomUUID() : editingId!,
      date: formData.date,
      unit: formData.unit,
      cpflKwh: Number(formData.cpflKwh) || 0,
      cpflCost: Number(formData.cpflCost) || 0,
      floraKwh: Number(formData.floraKwh) || 0,
      floraCost: Number(formData.floraCost) || 0,
      floraSavings: Number(formData.floraSavings) || 0,
    };

    if (editingId === 'NEW') {
      onAdd(recordData);
    } else {
      onUpdate(recordData);
    }
    setEditingId(null);
    setFormData({});
  };

  const handleDeleteClick = (id: string) => {
    if (window.confirm('Tem certeza que deseja excluir este registro?')) {
      onDelete(id);
    }
  };

  // Filter Data for Modal
  const filteredManageData = useMemo(() => {
    return data.filter(d => {
      const matchUnit = modalFilterUnit === 'All' || d.unit === modalFilterUnit;
      const searchLower = modalSearchTerm.toLowerCase();
      const matchSearch = modalSearchTerm === '' ||
        d.date.includes(searchLower) ||
        d.unit.toLowerCase().includes(searchLower);
      return matchUnit && matchSearch;
    }).sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime());
  }, [data, modalFilterUnit, modalSearchTerm]);

  return (
    <div ref={dashboardRef} className="space-y-8 animate-fade-in relative pb-10">

      {/* Header Controls */}
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700">
        <div className="flex items-center gap-4">
          <div className="flex items-center justify-center w-12 h-12 rounded-xl bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400">
            <Zap size={24} strokeWidth={2.5} />
          </div>
          <div>
            <h2 className="text-xl font-bold text-slate-800 dark:text-white">Dashboard de Energia</h2>
            <p className="text-sm text-slate-500 dark:text-slate-400 font-medium">Gestão de consumo e mercado livre (Flora)</p>
          </div>
        </div>

        <div className="flex flex-wrap gap-3">
          <select
            value={selectedUnit}
            onChange={(e) => setSelectedUnit(e.target.value)}
            className="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-sm font-semibold text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-ctdi-blue/20 hover:border-ctdi-blue/50 transition-all cursor-pointer"
          >
            <option value="All">Todas as Unidades</option>
            {units.map(u => <option key={u} value={u}>{u}</option>)}
          </select>
          <select
            value={selectedYear}
            onChange={(e) => setSelectedYear(e.target.value)}
            className="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-sm font-semibold text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-ctdi-blue/20 hover:border-ctdi-blue/50 transition-all cursor-pointer"
          >
            {availableYears.map(year => (
              <option key={year} value={year}>{year}</option>
            ))}
          </select>

          {userRole === 'manager' && (
            <button
              onClick={() => setIsManageModalOpen(true)}
              className="no-export flex items-center gap-2 px-5 py-2.5 bg-slate-800 dark:bg-slate-700 text-white rounded-xl shadow-lg shadow-slate-200 dark:shadow-none hover:bg-slate-700 dark:hover:bg-slate-600 hover:shadow-xl transition-all font-medium text-sm"
            >
              <Edit2 size={16} />
              Gerenciar Dados
            </button>
          )}

          <button
            onClick={handleAnalyze}
            disabled={loadingInsights}
            className="no-export flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-ctdi-blue to-blue-600 text-white rounded-xl shadow-lg shadow-blue-200 dark:shadow-none hover:shadow-blue-300 hover:-translate-y-0.5 transition-all disabled:opacity-70 disabled:translate-y-0 font-medium text-sm"
          >
            <Sparkles size={16} />
            {loadingInsights ? 'Processando...' : 'IA Insights'}
          </button>

          <button
            onClick={handleExportDashboard}
            disabled={isExporting}
            className="no-export flex items-center gap-2 px-5 py-2.5 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-600 rounded-xl shadow-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition-all font-medium text-sm disabled:opacity-50"
            title="Baixar Relatório Completo"
          >
            {isExporting ? <Loader2 size={16} className="animate-spin" /> : <Download size={16} />}
            {isExporting ? 'Gerando...' : 'Exportar'}
          </button>
        </div>
      </div>

      {insights && (
        <div className="bg-gradient-to-br from-indigo-50 to-white dark:from-indigo-900/40 dark:to-slate-800 border border-indigo-100 dark:border-indigo-800 p-6 rounded-2xl shadow-lg shadow-indigo-100/50 dark:shadow-none animate-fade-in relative overflow-hidden">
          <div className="absolute top-0 right-0 p-4 opacity-10">
            <Sparkles size={100} />
          </div>
          <button onClick={() => setInsights(null)} className="no-export absolute top-4 right-4 text-indigo-300 hover:text-indigo-600 dark:hover:text-indigo-200 transition-colors">✕</button>
          <h4 className="flex items-center gap-2 font-bold text-indigo-900 dark:text-indigo-200 mb-3 text-lg">
            <Sparkles size={20} className="text-indigo-600 dark:text-indigo-400" />
            Análise de Inteligência Artificial
          </h4>
          <div className="prose prose-sm text-indigo-800/80 dark:text-indigo-200/80 max-w-none whitespace-pre-line leading-relaxed font-medium">
            {insights}
          </div>
        </div>
      )}

      {/* Metric Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <MetricCard
          title="Consumo Total"
          value={`${(stats.totalKwh / 1000).toFixed(1)} MWh`}
          icon={Zap}
          color="blue"
        />

        {/* CUSTOM GOAL CARD */}
        <div className="relative overflow-hidden rounded-2xl p-6 text-white shadow-lg bg-gradient-to-br from-violet-600 to-purple-700 shadow-violet-200 dark:shadow-none flex flex-col justify-between group">
          {/* Background Decoration */}
          <div className="absolute -right-6 -top-6 opacity-10 transition-transform duration-500 group-hover:scale-110 group-hover:rotate-12 pointer-events-none">
            <Leaf size={140} />
          </div>

          <div className="relative z-10">
            <div className="flex justify-between items-start mb-2">
              <div className="flex items-center gap-2 mb-2 opacity-90">
                <div className="p-1.5 rounded-lg bg-white/20 backdrop-blur-sm">
                  <Leaf size={18} />
                </div>
                <p className="text-xs font-bold uppercase tracking-wider">Meta de Renováveis</p>
              </div>
            </div>

            <div className="mb-4">
              <div className="flex items-baseline gap-2">
                <h3 className="text-3xl font-bold text-white">{stats.renewablePercentage.toFixed(1)}%</h3>
                <span className="text-sm font-medium text-white/80">Atingido (Média)</span>
              </div>
            </div>

            <div>
              {/* Progress Bar Container */}
              <div className="relative h-3 bg-black/20 rounded-full overflow-hidden mb-2">
                <div
                  className={`absolute top-0 left-0 h-full transition-all duration-1000 ${stats.renewablePercentage >= GOAL_PERCENTAGE ? 'bg-emerald-300' : 'bg-rose-300'}`}
                  style={{ width: `${stats.renewablePercentage}%` }}
                ></div>
                {/* Marker for Goal */}
                <div className="absolute top-0 bottom-0 w-0.5 bg-white z-10 shadow-[0_0_10px_rgba(255,255,255,0.8)]" style={{ left: `${GOAL_PERCENTAGE}%` }}></div>
              </div>

              <div className="flex justify-between text-[10px] font-bold text-white/60 uppercase tracking-wider">
                <span>0%</span>
                <span className={stats.renewablePercentage >= GOAL_PERCENTAGE ? "text-emerald-100" : "text-rose-100"}>
                  {stats.renewablePercentage >= GOAL_PERCENTAGE ? 'Meta Atingida' : 'Abaixo da Meta'} ({GOAL_PERCENTAGE}%)
                </span>
                <span>100%</span>
              </div>
            </div>
          </div>
        </div>

        <MetricCard
          title="Economia Estimada"
          value={`R$ ${stats.totalSavings.toLocaleString('pt-BR', { maximumFractionDigits: 0 })}`}
          icon={Coins}
          color="green"
        />
        <MetricCard
          title="Custo Total"
          value={`R$ ${stats.totalCost.toLocaleString('pt-BR', { maximumFractionDigits: 0 })}`}
          icon={DollarSign}
          color="slate"
        />
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {/* Main Chart: Stacked Bar for Source Breakdown */}
        <div ref={chartRef1} className="lg:col-span-2 bg-white dark:bg-slate-800/50 dark:backdrop-blur-sm p-6 rounded-3xl shadow-[0_2px_20px_rgba(0,0,0,0.04)] dark:shadow-none border border-slate-100 dark:border-slate-700 flex flex-col h-[450px] relative group">
          <div className="mb-6 flex justify-between items-start">
            <div>
              <h3 className="text-xl font-bold text-slate-800 dark:text-white">Fonte de Energia: Convencional vs Renovável</h3>
              <p className="text-sm text-slate-400 dark:text-slate-500 mt-1">Comparativo mensal de consumo (kWh).</p>
            </div>
            <button onClick={() => handleDownloadChart(chartRef1, 'fonte-energia')} className="no-export p-2 text-slate-400 hover:text-ctdi-blue hover:bg-blue-50 dark:hover:bg-slate-700 rounded-lg transition-colors opacity-0 group-hover:opacity-100">
              <Camera size={20} />
            </button>
          </div>

          <ResponsiveContainer width="100%" height="100%">
            <BarChart data={chartData} margin={{ top: 20, right: 30, left: 0, bottom: 0 }}>
              <CartesianGrid strokeDasharray="3 3" vertical={false} stroke={chartColors.grid} />
              <XAxis
                dataKey="month"
                stroke={chartColors.text}
                fontSize={11}
                tickLine={false}
                axisLine={false}
                dy={10}
              />
              <YAxis
                stroke={chartColors.text}
                fontSize={11}
                tickLine={false}
                axisLine={false}
                tickFormatter={(v) => `${(v / 1000).toFixed(0)}k`}
              />
              <Tooltip
                cursor={{ fill: theme === 'dark' ? '#334155' : '#f8fafc' }}
                contentStyle={{
                  backgroundColor: theme === 'dark' ? '#1e293b' : '#fff',
                  borderColor: theme === 'dark' ? '#334155' : '#e2e8f0',
                  borderRadius: '12px',
                  boxShadow: '0 10px 15px -3px rgba(0, 0, 0, 0.1)'
                }}
              />
              <Legend iconType="circle" wrapperStyle={{ paddingTop: '20px', fontSize: '12px' }} />
              <Bar
                dataKey="CPFL (Convencional)"
                stackId="a"
                fill={chartColors.cpfl}
                radius={[0, 0, 4, 4]}
                barSize={32}
              />
              <Bar
                dataKey="Flora (Renovável)"
                stackId="a"
                fill={chartColors.flora}
                radius={[4, 4, 0, 0]}
                barSize={32}
              />
            </BarChart>
          </ResponsiveContainer>
        </div>

        {/* Chart 2: Savings Area Chart */}
        <div ref={chartRef2} className="bg-white dark:bg-slate-800/50 dark:backdrop-blur-sm p-6 rounded-3xl shadow-[0_2px_20px_rgba(0,0,0,0.04)] dark:shadow-none border border-slate-100 dark:border-slate-700 flex flex-col h-[400px] relative group">
          <div className="mb-6 flex justify-between items-start">
            <div>
              <h3 className="text-xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <Coins size={20} className="text-yellow-500" /> Evolução da Economia
              </h3>
              <p className="text-sm text-slate-400 dark:text-slate-500 mt-1">Economia gerada pelo Mercado Livre (R$)</p>
            </div>
            <button onClick={() => handleDownloadChart(chartRef2, 'evolucao-economia')} className="no-export p-2 text-slate-400 hover:text-ctdi-blue hover:bg-blue-50 dark:hover:bg-slate-700 rounded-lg transition-colors opacity-0 group-hover:opacity-100">
              <Camera size={20} />
            </button>
          </div>
          <ResponsiveContainer width="100%" height="100%">
            <AreaChart data={savingsData}>
              <defs>
                <linearGradient id="colorSavings" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="5%" stopColor="#eab308" stopOpacity={0.3} />
                  <stop offset="95%" stopColor="#eab308" stopOpacity={0} />
                </linearGradient>
              </defs>
              <CartesianGrid strokeDasharray="3 3" vertical={false} stroke={chartColors.grid} />
              <XAxis dataKey="month" stroke={chartColors.text} fontSize={11} tickLine={false} axisLine={false} dy={10} />
              <YAxis stroke={chartColors.text} fontSize={11} tickLine={false} axisLine={false} tickFormatter={(v) => `R$${(v / 1000).toFixed(0)}k`} />
              <Tooltip formatter={(value: number) => [`R$ ${value.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}`, 'Economia']} />
              <Area type="monotone" dataKey="savings" name="Economia" stroke="#eab308" strokeWidth={3} fill="url(#colorSavings)" />
            </AreaChart>
          </ResponsiveContainer>
        </div>

        {/* Chart 3: Renewable Share Trend (Reverted to Bars) */}
        <div ref={chartRef3} className="bg-white dark:bg-slate-800/50 dark:backdrop-blur-sm p-6 rounded-3xl shadow-[0_2px_20px_rgba(0,0,0,0.04)] dark:shadow-none border border-slate-100 dark:border-slate-700 flex flex-col h-[400px] relative group">
          <div className="mb-6 flex justify-between items-start">
            <div>
              <h3 className="text-xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
                <Leaf size={20} className="text-ctdi-green" /> Sustentabilidade
              </h3>
              <p className="text-sm text-slate-400 dark:text-slate-500 mt-1">% de Energia Renovável vs Meta</p>
            </div>
            <button onClick={() => handleDownloadChart(chartRef3, 'sustentabilidade')} className="no-export p-2 text-slate-400 hover:text-ctdi-blue hover:bg-blue-50 dark:hover:bg-slate-700 rounded-lg transition-colors opacity-0 group-hover:opacity-100">
              <Camera size={20} />
            </button>
          </div>

          <ResponsiveContainer width="100%" height="100%">
            <BarChart data={chartData.map(d => ({ month: d.month, perc: d.total > 0 ? (d['Flora (Renovável)'] / d.total) * 100 : 0 }))} margin={{ top: 10, right: 10, left: 0, bottom: 0 }}>
              <CartesianGrid strokeDasharray="3 3" vertical={false} stroke={chartColors.grid} />
              <XAxis dataKey="month" stroke={chartColors.text} fontSize={11} tickLine={false} axisLine={false} dy={10} />
              <YAxis stroke={chartColors.text} fontSize={11} tickLine={false} axisLine={false} domain={[0, 100]} />
              <Tooltip
                cursor={{ fill: theme === 'dark' ? '#334155' : '#f8fafc' }}
                formatter={(value: number) => [`${value.toFixed(1)}%`, 'Renovável']}
                contentStyle={{
                  backgroundColor: theme === 'dark' ? '#1e293b' : '#fff',
                  borderColor: theme === 'dark' ? '#334155' : '#e2e8f0',
                  borderRadius: '12px'
                }}
              />

              {/* Reference Line for Goal - ORANGE */}
              <ReferenceLine
                y={GOAL_PERCENTAGE}
                stroke="#f97316"
                strokeDasharray="5 5"
                label={{ position: 'top', value: `Meta (${GOAL_PERCENTAGE}%)`, fill: '#f97316', fontSize: 10, fontWeight: 'bold' }}
              />

              <Bar dataKey="perc" fill="#78be20" radius={[4, 4, 0, 0]} barSize={32}>
                {chartData.map((d, index) => {
                  const val = d.total > 0 ? (d['Flora (Renovável)'] / d.total) * 100 : 0;
                  return <Cell key={`cell-${index}`} fill={val >= GOAL_PERCENTAGE ? '#78be20' : '#94a3b8'} />;
                })}
              </Bar>
            </BarChart>
          </ResponsiveContainer>
        </div>

      </div>

      {/* Management Modal */}
      {isManageModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm animate-fade-in">
          <div className="bg-white dark:bg-slate-800 w-full max-w-5xl h-[80vh] rounded-2xl shadow-2xl flex flex-col overflow-hidden border border-slate-200 dark:border-slate-700">
            {/* Header */}
            <div className="p-6 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between bg-white dark:bg-slate-800">
              <div>
                <h2 className="text-xl font-bold text-slate-800 dark:text-white flex items-center gap-2">
                  <Table className="text-ctdi-blue" size={20} />
                  Gerenciamento de Dados
                </h2>
                <p className="text-sm text-slate-500 dark:text-slate-400">Adicione, edite ou remova registros de energia manualmente.</p>
              </div>
              <button onClick={() => setIsManageModalOpen(false)} className="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 transition-colors">
                <X size={20} />
              </button>
            </div>
            {/* Body */}
            <div className="flex-1 overflow-auto bg-slate-50/50 dark:bg-slate-900/50 p-6">
              {/* Filters */}
              {!editingId && (
                <div className="mb-6 flex flex-col md:flex-row gap-4 justify-between items-center bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
                  <div className="flex flex-1 items-center gap-4 w-full">
                    <div className="relative flex-1 max-w-sm">
                      <Search className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" size={18} />
                      <input type="text" placeholder="Buscar..." value={modalSearchTerm} onChange={(e) => setModalSearchTerm(e.target.value)} className="w-full pl-10 pr-4 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white text-sm outline-none focus:ring-2 focus:ring-ctdi-blue" />
                    </div>
                    <div className="relative">
                      <Filter className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" size={18} />
                      <select value={modalFilterUnit} onChange={(e) => setModalFilterUnit(e.target.value)} className="pl-10 pr-8 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white text-sm outline-none focus:ring-2 focus:ring-ctdi-blue appearance-none">
                        <option value="All">Todas as Unidades</option>
                        {units.map(u => <option key={u} value={u}>{u}</option>)}
                      </select>
                    </div>
                  </div>
                  <button onClick={handleAddNewClick} className="flex items-center gap-2 px-4 py-2 bg-ctdi-green hover:bg-green-600 text-white rounded-lg shadow-sm transition-all text-sm font-semibold whitespace-nowrap"><Plus size={16} /> Adicionar Registro</button>
                </div>
              )}

              {/* Form */}
              {editingId && (
                <div className="mb-6 bg-white dark:bg-slate-800 p-6 rounded-xl border border-blue-200 dark:border-slate-600 shadow-sm animate-fade-in-up">
                  <h3 className="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-4 border-b border-slate-100 dark:border-slate-700 pb-2">{editingId === 'NEW' ? 'Novo Registro' : 'Editando Registro'}</h3>
                  <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div><label className="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Data</label><input type="date" value={formData.date || ''} onChange={e => setFormData({ ...formData, date: e.target.value })} className="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-ctdi-blue outline-none" /></div>
                    <div><label className="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Unidade</label><select value={formData.unit || units[0]} onChange={e => setFormData({ ...formData, unit: e.target.value })} className="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-ctdi-blue outline-none">{units.map(u => <option key={u} value={u}>{u}</option>)}</select></div>
                  </div>
                  <div className="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <div><label className="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">CPFL (kWh)</label><input type="number" value={formData.cpflKwh} onChange={e => setFormData({ ...formData, cpflKwh: Number(e.target.value) })} className="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-ctdi-blue outline-none" /></div>
                    <div><label className="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">CPFL (R$)</label><input type="number" step="0.01" value={formData.cpflCost} onChange={e => setFormData({ ...formData, cpflCost: Number(e.target.value) })} className="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-ctdi-blue outline-none" /></div>
                    <div><label className="block text-xs font-semibold text-ctdi-green mb-1">Flora (kWh)</label><input type="number" value={formData.floraKwh} onChange={e => setFormData({ ...formData, floraKwh: Number(e.target.value) })} className="w-full px-3 py-2 rounded-lg border border-green-200 dark:border-green-900 bg-green-50 dark:bg-green-900/20 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-ctdi-green outline-none" /></div>
                    <div><label className="block text-xs font-semibold text-ctdi-green mb-1">Flora (R$)</label><input type="number" step="0.01" value={formData.floraCost} onChange={e => setFormData({ ...formData, floraCost: Number(e.target.value) })} className="w-full px-3 py-2 rounded-lg border border-green-200 dark:border-green-900 bg-green-50 dark:bg-green-900/20 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-ctdi-green outline-none" /></div>
                    <div><label className="block text-xs font-semibold text-yellow-600 dark:text-yellow-500 mb-1">Economia (R$)</label><input type="number" step="0.01" value={formData.floraSavings} onChange={e => setFormData({ ...formData, floraSavings: Number(e.target.value) })} className="w-full px-3 py-2 rounded-lg border border-yellow-200 dark:border-yellow-900 bg-yellow-50 dark:bg-yellow-900/20 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-yellow-500 outline-none" /></div>
                  </div>
                  <div className="flex gap-3 justify-end mt-4">
                    <button onClick={() => { setEditingId(null); setFormData({}); }} className="px-4 py-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 font-medium text-sm">Cancelar</button>
                    <button onClick={handleSave} className="flex items-center gap-2 px-6 py-2 bg-ctdi-blue hover:bg-blue-700 text-white rounded-lg shadow-md transition-all text-sm font-semibold"><Save size={16} /> Salvar</button>
                  </div>
                </div>
              )}

              {/* Table */}
              <div className="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                <table className="w-full text-left border-collapse">
                  <thead><tr className="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 font-semibold"><th className="p-4">Data</th><th className="p-4">Unidade</th><th className="p-4">Consumo (kWh)</th><th className="p-4">Custo Total (R$)</th><th className="p-4">Economia (R$)</th><th className="p-4 text-right">Ações</th></tr></thead>
                  <tbody className="divide-y divide-slate-100 dark:divide-slate-700">
                    {filteredManageData.map(record => (
                      <tr key={record.id} className="hover:bg-blue-50/50 dark:hover:bg-slate-700/50 transition-colors group">
                        <td className="p-4 text-sm font-medium text-slate-700 dark:text-slate-300">{new Date(record.date).toLocaleDateString('pt-BR')}</td>
                        <td className="p-4 text-sm text-slate-600 dark:text-slate-400">{record.unit}</td>
                        <td className="p-4 text-sm text-slate-700 dark:text-slate-300 font-mono">{(record.cpflKwh + record.floraKwh).toLocaleString()}</td>
                        <td className="p-4 text-sm text-slate-700 dark:text-slate-300 font-mono">R$ {(record.cpflCost + record.floraCost).toLocaleString('pt-BR', { minimumFractionDigits: 2 })}</td>
                        <td className="p-4 text-sm font-bold text-emerald-600 dark:text-emerald-400 font-mono">R$ {record.floraSavings.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}</td>
                        <td className="p-4 text-right"><div className="flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity"><button onClick={() => handleEditClick(record)} className="p-1.5 text-blue-600 hover:bg-blue-50 rounded"><Edit2 size={16} /></button><button onClick={() => handleDeleteClick(record.id)} className="p-1.5 text-rose-500 hover:bg-rose-50 rounded"><Trash2 size={16} /></button></div></td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      )}
    </div>
  );
};