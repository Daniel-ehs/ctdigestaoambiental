

import React, { useMemo, useState, useRef } from 'react';
import {
  XAxis, YAxis, CartesianGrid, Tooltip, ResponsiveContainer, BarChart, Bar, ComposedChart, Line, Area, AreaChart, Cell, ReferenceLine
} from 'recharts';
import { Droplets, DollarSign, Sparkles, TrendingUp, AlertTriangle, Building2, Edit2, Plus, Trash2, X, Save, Table, Search, Filter, Camera, Download, Loader2 } from 'lucide-react';
import { WaterRecord, UserRole } from '../types';
import { MetricCard } from './Metrics';
import { generateInsights } from '../services/geminiService';
import html2canvas from 'html2canvas';

interface Props {
  data: WaterRecord[];
  units: string[];
  goal: number; // Consumption Limit Goal per Unit (m3)
  onUpdate: (record: WaterRecord) => void;
  onAdd: (record: WaterRecord) => void;
  onDelete: (id: string) => void;
  userRole: UserRole;
  theme: 'light' | 'dark';
}

const WaterDashboard: React.FC<Props> = ({ data, units, goal, onUpdate, onAdd, onDelete, userRole, theme }) => {
  const currentYear = new Date().getFullYear().toString();
  const [selectedUnit, setSelectedUnit] = useState<string | 'All'>('All');
  const [selectedYear, setSelectedYear] = useState<string>(currentYear);
  const [insights, setInsights] = useState<string | null>(null);
  const [loadingInsights, setLoadingInsights] = useState(false);
  const [isExporting, setIsExporting] = useState(false);

  // Chart Refs
  const dashboardRef = useRef<HTMLDivElement>(null);
  const chartRef1 = useRef<HTMLDivElement>(null);
  const chartRef2 = useRef<HTMLDivElement>(null);

  // Dynamic Goal Calculation:
  // Goal is defined as "Per Unit".
  // If "All Units" is selected, the Total Goal = goal * number_of_units.
  // If specific unit is selected, Total Goal = goal.
  const calculatedGoal = useMemo(() => {
    if (selectedUnit !== 'All') return goal;
    // Assuming 'units' prop contains all active units available in the system
    return goal * units.length;
  }, [goal, selectedUnit, units]);

  const GOAL_LIMIT = calculatedGoal;

  // Modal State
  const [isManageModalOpen, setIsManageModalOpen] = useState(false);
  const [editingId, setEditingId] = useState<string | null>(null);
  const [formData, setFormData] = useState<Partial<WaterRecord>>({});

  // Modal Filter State
  const [modalSearchTerm, setModalSearchTerm] = useState('');
  const [modalFilterUnit, setModalFilterUnit] = useState<string | 'All'>('All');

  // Calculate available years from data + current year
  const availableYears = useMemo(() => {
    const years = new Set<string>();
    years.add(currentYear);
    data.forEach(d => years.add(d.date.substring(0, 4)));
    return Array.from(years).sort().reverse();
  }, [data, currentYear]);

  // Filter Data
  const filteredData = useMemo(() => {
    return data.filter(d => {
      const matchUnit = selectedUnit === 'All' || d.unit === selectedUnit;
      const matchYear = d.date.startsWith(selectedYear);
      return matchUnit && matchYear;
    }).sort((a, b) => new Date(a.date).getTime() - new Date(b.date).getTime());
  }, [data, selectedUnit, selectedYear]);

  // Chart Data: Aggregated by Month
  const chartData = useMemo(() => {
    const months = Array.from(new Set(filteredData.map(d => d.date.substring(0, 7)))).sort();
    return months.map(month => {
      const records = filteredData.filter(d => d.date.startsWith(month));
      const dateObj = new Date(month + '-01T12:00:00');

      return {
        month: dateObj.toLocaleString('pt-BR', { month: 'short' }).toUpperCase(),
        fullDate: dateObj.toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' }),
        volume: records.reduce((sum, r) => sum + r.volume, 0),
        cost: records.reduce((sum, r) => sum + r.cost, 0),
      };
    });
  }, [filteredData]);

  // Stats for Cards
  const stats = useMemo(() => {
    const totalVol = filteredData.reduce((sum, r) => sum + r.volume, 0);
    const totalCost = filteredData.reduce((sum, r) => sum + r.cost, 0);

    // Average Monthly Consumption
    const numberOfMonths = chartData.length || 1;
    const avgMonthlyVol = totalVol / numberOfMonths;

    // Calculate month-over-month change
    let trend = 0;
    if (chartData.length >= 2) {
      const last = chartData[chartData.length - 1].volume;
      const prev = chartData[chartData.length - 2].volume;
      if (prev > 0) trend = ((last - prev) / prev) * 100;
    }

    // Goal Comparison (Average vs Limit)
    const isBelowGoal = avgMonthlyVol <= GOAL_LIMIT;
    // Percentage used of the "Limit Budget"
    const goalPerc = GOAL_LIMIT > 0 ? (avgMonthlyVol / GOAL_LIMIT) * 100 : 0;

    return { totalVol, totalCost, avgMonthlyVol, trend, isBelowGoal, goalPerc };
  }, [filteredData, chartData, GOAL_LIMIT]);

  // Data by Unit (Summary) - SORTED by Volume Descending for Ranking
  const unitBreakdown = useMemo(() => {
    if (selectedUnit !== 'All') return [];
    const breakdown = units.map(u => {
      const unitRecs = filteredData.filter(d => d.unit === u);
      const vol = unitRecs.reduce((s, r) => s + r.volume, 0);
      const cost = unitRecs.reduce((s, r) => s + r.cost, 0);
      return { name: u, volume: vol, cost: cost };
    });
    return breakdown.sort((a, b) => b.volume - a.volume);
  }, [filteredData, selectedUnit, units]);

  const handleAnalyze = async () => {
    setLoadingInsights(true);
    const context = JSON.stringify(chartData.map(c => ({ m: c.month, v: c.volume, c: c.cost })));
    const result = await generateInsights(context, `Consumo de Água para ${selectedUnit === 'All' ? 'Todas as Unidades' : selectedUnit}. Atenção à média mensal de ${stats.avgMonthlyVol.toFixed(1)}m³ versus meta de ${GOAL_LIMIT}m³.`);
    setInsights(result);
    setLoadingInsights(false);
  };

  const handleDownloadChart = async (ref: React.RefObject<HTMLDivElement>, fileName: string) => {
    if (ref.current) {
      try {
        const canvas = await html2canvas(ref.current, {
          backgroundColor: theme === 'dark' ? '#1e293b' : '#ffffff',
          scale: 2
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
        await new Promise(resolve => setTimeout(resolve, 100));

        const canvas = await html2canvas(dashboardRef.current, {
          scale: 2,
          backgroundColor: theme === 'dark' ? '#0f172a' : '#f8fafc',
          ignoreElements: (element) => element.classList.contains('no-export'),
          useCORS: true
        });
        const link = document.createElement('a');
        link.download = `Relatorio_Agua_${selectedUnit}_${selectedYear}.png`;
        link.href = canvas.toDataURL('image/png');
        link.click();
      } catch (error) {
        console.error("Export error:", error);
        alert("Erro ao exportar dashboard.");
      } finally {
        setIsExporting(false);
      }
    }
  };

  // --- CRUD Operations ---
  const handleEditClick = (record: WaterRecord) => {
    setEditingId(record.id);
    setFormData(record);
  };

  const handleAddNewClick = () => {
    setEditingId('NEW');
    setFormData({
      date: new Date().toISOString().split('T')[0],
      unit: units[0] || 'Galpão 6',
      volume: 0,
      cost: 0
    });
  };

  const handleSave = () => {
    if (!formData.date || !formData.unit || formData.volume === undefined || formData.cost === undefined) return;

    if (editingId === 'NEW') {
      const newRecord: WaterRecord = {
        id: crypto.randomUUID(),
        date: formData.date,
        unit: formData.unit,
        volume: Number(formData.volume),
        cost: Number(formData.cost)
      };
      onAdd(newRecord);
    } else if (editingId) {
      const updatedRecord: WaterRecord = {
        id: editingId,
        date: formData.date,
        unit: formData.unit,
        volume: Number(formData.volume),
        cost: Number(formData.cost)
      };
      onUpdate(updatedRecord);
    }
    setEditingId(null);
    setFormData({});
  };

  const handleDeleteClick = (id: string) => {
    if (window.confirm('Tem certeza que deseja excluir este registro?')) {
      onDelete(id);
    }
  };

  // Filtered Data for Modal
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

  // Custom Tooltip
  const CustomTooltip = ({ active, payload, label }: any) => {
    if (active && payload && payload.length) {
      return (
        <div className="bg-white/90 dark:bg-slate-800/90 backdrop-blur-md p-4 border border-slate-100 dark:border-slate-700 rounded-xl shadow-xl">
          <p className="text-sm font-bold text-slate-700 dark:text-slate-200 mb-2">{label}</p>
          <div className="space-y-1">
            <p className="text-sm text-ctdi-blue dark:text-blue-400 flex items-center justify-between gap-4">
              <span>Volume:</span>
              <span className="font-mono font-bold">{payload[0].value.toLocaleString()} m³</span>
            </p>
            {payload[1] && (
              <p className="text-sm text-rose-500 dark:text-rose-400 flex items-center justify-between gap-4">
                <span>Custo:</span>
                <span className="font-mono font-bold">R$ {payload[1].value.toLocaleString('pt-BR', { maximumFractionDigits: 0 })}</span>
              </p>
            )}
          </div>
        </div>
      );
    }
    return null;
  };

  const chartColors = {
    grid: theme === 'dark' ? '#334155' : '#f1f5f9',
    text: theme === 'dark' ? '#94a3b8' : '#64748b',
    areaFill: theme === 'dark' ? '#0f172a' : '#eff6ff', // Light blue bg for area
    areaStroke: '#3b82f6', // Blue 500
    line: '#f43f5e',
  };

  // Vivid Colors for Ranking
  const COLORS = ['#0ea5e9', '#06b6d4', '#6366f1', '#8b5cf6', '#d946ef'];

  return (
    <div ref={dashboardRef} className="space-y-8 animate-fade-in relative pb-10">

      {/* Header Controls */}
      <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700">
        <div className="flex items-center gap-4">
          <div className="flex items-center justify-center w-12 h-12 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-ctdi-blue dark:text-blue-400">
            <Droplets size={24} strokeWidth={2.5} />
          </div>
          <div>
            <h2 className="text-xl font-bold text-slate-800 dark:text-white">Dashboard de Recursos Hídricos</h2>
            <p className="text-sm text-slate-500 dark:text-slate-400 font-medium">Monitoramento de consumo e custo mensal</p>
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
            {isExporting ? '...' : ''}
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

      {/* Metrics Row */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <MetricCard
          title="Volume Acumulado"
          value={`${stats.totalVol.toLocaleString()} m³`}
          icon={Droplets}
          color="blue"
          subtext={`Em ${selectedYear}`}
        />

        {/* GOAL CARD */}
        <div className="relative overflow-hidden rounded-2xl p-6 text-white shadow-lg bg-gradient-to-br from-cyan-500 to-blue-600 shadow-cyan-200 dark:shadow-none flex flex-col justify-between group">
          <div className="absolute -right-6 -top-6 opacity-10 transition-transform duration-500 group-hover:scale-110 group-hover:rotate-12 pointer-events-none">
            <Building2 size={140} />
          </div>

          <div className="relative z-10">
            <div className="flex justify-between items-start mb-2">
              <div className="flex items-center gap-2 mb-2 opacity-90">
                <div className="p-1.5 rounded-lg bg-white/20 backdrop-blur-sm">
                  <TrendingUp size={18} />
                </div>
                <p className="text-xs font-bold uppercase tracking-wider">Meta (Média Mensal)</p>
              </div>
            </div>

            <div className="mb-4">
              <div className="flex items-baseline gap-2">
                <h3 className="text-3xl font-bold text-white">{stats.avgMonthlyVol.toFixed(1)} <span className="text-base font-medium">m³</span></h3>
                <span className="text-sm font-medium text-white/80">Média Atual</span>
              </div>
            </div>

            <div>
              {/* Progress Bar Container */}
              <div className="relative h-3 bg-black/20 rounded-full overflow-hidden mb-2">
                <div
                  className={`absolute top-0 left-0 h-full transition-all duration-1000 ${stats.isBelowGoal ? 'bg-emerald-300' : 'bg-rose-300'}`}
                  style={{ width: `${Math.min(stats.goalPerc, 100)}%` }}
                ></div>
                <div className="absolute top-0 bottom-0 w-0.5 bg-white z-10 shadow-[0_0_10px_rgba(255,255,255,0.8)]" style={{ left: `${Math.min((GOAL_LIMIT / Math.max(stats.avgMonthlyVol, GOAL_LIMIT)) * 100, 100)}%` }}></div>
              </div>

              <div className="flex justify-between text-[10px] font-bold text-white/60 uppercase tracking-wider">
                <span>0 m³</span>
                <span className={stats.isBelowGoal ? "text-emerald-200" : "text-rose-200"}>
                  {stats.isBelowGoal ? 'Dentro do Limite' : 'Acima do Limite'} ({GOAL_LIMIT} m³)
                </span>
              </div>
            </div>
          </div>
        </div>

        <MetricCard
          title="Custo Acumulado"
          value={`R$ ${stats.totalCost.toLocaleString('pt-BR', { maximumFractionDigits: 0 })}`}
          icon={DollarSign}
          color="slate"
        />
        <MetricCard
          title="Variação Mensal"
          value={`${stats.trend.toFixed(2)}%`}
          change={stats.trend}
          icon={TrendingUp}
          color={stats.trend > 0 ? "orange" : "green"}
          subtext="Vs. mês anterior"
        />
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {/* Main Chart: Area Chart for Volume Clarity */}
        <div ref={chartRef1} className="lg:col-span-2 bg-white dark:bg-slate-800/50 dark:backdrop-blur-sm p-8 rounded-3xl shadow-[0_2px_20px_rgba(0,0,0,0.04)] dark:shadow-none border border-slate-100 dark:border-slate-700 flex flex-col h-[450px] relative group">
          <div className="flex justify-between items-start mb-8">
            <div>
              <h3 className="text-xl font-bold text-slate-800 dark:text-white">Evolução: Volume vs Custo</h3>
              <p className="text-sm text-slate-400 dark:text-slate-500 mt-1">Análise visual da tendência de consumo.</p>
            </div>
            <div className="flex items-center gap-4 text-xs font-semibold text-slate-500 dark:text-slate-400">
              <div className="flex items-center gap-2">
                <span className="w-3 h-3 rounded-full bg-blue-500"></span> Volume (m³)
              </div>
              <div className="flex items-center gap-2">
                <span className="w-4 h-0.5 bg-rose-500"></span> Custo (R$)
              </div>
              <button onClick={() => handleDownloadChart(chartRef1, 'evolucao-agua')} className="no-export p-2 text-slate-400 hover:text-ctdi-blue hover:bg-blue-50 dark:hover:bg-slate-700 rounded-lg transition-colors opacity-0 group-hover:opacity-100">
                <Camera size={20} />
              </button>
            </div>
          </div>

          <ResponsiveContainer width="100%" height="100%">
            <AreaChart data={chartData} margin={{ top: 10, right: 30, left: 0, bottom: 0 }}>
              <defs>
                <linearGradient id="colorVol" x1="0" y1="0" x2="0" y2="1">
                  <stop offset="5%" stopColor="#3b82f6" stopOpacity={0.6} />
                  <stop offset="95%" stopColor="#3b82f6" stopOpacity={0} />
                </linearGradient>
              </defs>
              <CartesianGrid strokeDasharray="3 3" vertical={false} stroke={chartColors.grid} />
              <XAxis
                dataKey="month"
                stroke={chartColors.text}
                fontSize={11}
                fontWeight={600}
                tickLine={false}
                axisLine={false}
                dy={10}
              />
              <YAxis
                yAxisId="left"
                stroke={chartColors.text}
                fontSize={11}
                tickLine={false}
                axisLine={false}
                label={{ value: 'm³', angle: -90, position: 'insideLeft', fill: chartColors.text }}
              />
              <YAxis
                yAxisId="right"
                orientation="right"
                stroke={chartColors.text}
                fontSize={11}
                tickLine={false}
                axisLine={false}
                tickFormatter={(v) => `k ${(v / 1000).toFixed(0)}`}
              />
              <Tooltip content={<CustomTooltip />} cursor={{ stroke: theme === 'dark' ? '#94a3b8' : '#64748b', strokeWidth: 1, strokeDasharray: '4 4' }} />

              {/* Reference Line for Max Consumption Goal (Orange) */}
              <ReferenceLine
                y={GOAL_LIMIT}
                yAxisId="left"
                stroke="#f97316"
                strokeWidth={2}
                strokeDasharray="5 5"
                label={{ value: `Meta: ${GOAL_LIMIT}m³`, position: 'top', fill: '#f97316', fontSize: 10, fontWeight: 'bold' }}
              />

              <Area
                yAxisId="left"
                type="monotone"
                dataKey="volume"
                stroke="#3b82f6"
                strokeWidth={2}
                fillOpacity={1}
                fill="url(#colorVol)"
              />
              <Line
                yAxisId="right"
                type="monotone"
                dataKey="cost"
                stroke={chartColors.line}
                strokeWidth={3}
                dot={false}
              />
            </AreaChart>
          </ResponsiveContainer>
        </div>

        {/* Breakdown Chart: Ranked & Colorful */}
        <div ref={chartRef2} className="bg-white dark:bg-slate-800/50 dark:backdrop-blur-sm p-8 rounded-3xl shadow-[0_2px_20px_rgba(0,0,0,0.04)] dark:shadow-none border border-slate-100 dark:border-slate-700 flex flex-col h-[450px] relative group">
          <div className="flex justify-between items-start mb-2">
            <h3 className="text-xl font-bold text-slate-800 dark:text-white">Ranking de Consumo</h3>
            <button onClick={() => handleDownloadChart(chartRef2, 'ranking-consumo')} className="no-export p-2 text-slate-400 hover:text-ctdi-blue hover:bg-blue-50 dark:hover:bg-slate-700 rounded-lg transition-colors opacity-0 group-hover:opacity-100">
              <Camera size={20} />
            </button>
          </div>
          <p className="text-sm text-slate-400 dark:text-slate-500 mb-6">Unidades que mais consomem (Acumulado)</p>

          {selectedUnit === 'All' ? (
            <ResponsiveContainer width="100%" height="100%">
              <BarChart data={unitBreakdown} layout="vertical" margin={{ left: 0, right: 30 }}>
                <CartesianGrid strokeDasharray="3 3" horizontal={true} vertical={false} stroke={chartColors.grid} />
                <XAxis type="number" hide />
                <YAxis dataKey="name" type="category" width={80} fontSize={11} tickLine={false} axisLine={false} fontWeight={600} stroke={chartColors.text} />
                <Tooltip
                  cursor={{ fill: 'transparent' }}
                  content={({ active, payload }) => {
                    if (active && payload && payload.length) {
                      return (
                        <div className="bg-slate-800 dark:bg-white text-white dark:text-slate-900 text-xs rounded-lg py-2 px-3 shadow-xl">
                          <div className="font-bold mb-1">{payload[0].payload.name}</div>
                          <div className="font-mono">Vol: {payload[0].value.toLocaleString()} m³</div>
                        </div>
                      )
                    }
                    return null;
                  }}
                />
                <Bar dataKey="volume" radius={[0, 4, 4, 0]} barSize={24}>
                  {unitBreakdown.map((entry, index) => (
                    <Cell key={`cell-${index}`} fill={COLORS[index % COLORS.length]} />
                  ))}
                </Bar>
              </BarChart>
            </ResponsiveContainer>
          ) : (
            <div className="flex-1 flex flex-col items-center justify-center text-slate-400 text-center px-4">
              <Building2 size={48} className="mb-4 opacity-20" />
              <p>Selecione "Todas as Unidades" para ver o comparativo.</p>
            </div>
          )}
        </div>
      </div>

      {/* Modal - keeping same structure */}
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
                <p className="text-sm text-slate-500 dark:text-slate-400">Adicione, edite ou remova registros de água manualmente.</p>
              </div>
              <button onClick={() => setIsManageModalOpen(false)} className="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 transition-colors">
                <X size={20} />
              </button>
            </div>
            {/* Body - using existing logic components */}
            <div className="flex-1 overflow-auto bg-slate-50/50 dark:bg-slate-900/50 p-6">
              {/* Filter Section */}
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
              {/* Edit Form */}
              {editingId && (
                <div className="mb-6 bg-white dark:bg-slate-800 p-6 rounded-xl border border-blue-200 dark:border-slate-600 shadow-sm animate-fade-in-up">
                  <h3 className="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-4 border-b border-slate-100 dark:border-slate-700 pb-2">{editingId === 'NEW' ? 'Novo Registro' : 'Editando Registro'}</h3>
                  <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div><label className="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Data</label><input type="date" value={formData.date || ''} onChange={e => setFormData({ ...formData, date: e.target.value })} className="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-ctdi-blue outline-none" /></div>
                    <div><label className="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Unidade</label><select value={formData.unit || units[0]} onChange={e => setFormData({ ...formData, unit: e.target.value })} className="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-ctdi-blue outline-none">{units.map(u => <option key={u} value={u}>{u}</option>)}</select></div>
                    <div><label className="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Volume (m³)</label><input type="number" value={formData.volume} onChange={e => setFormData({ ...formData, volume: Number(e.target.value) })} className="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-ctdi-blue outline-none" /></div>
                    <div><label className="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Custo (R$)</label><input type="number" step="0.01" value={formData.cost} onChange={e => setFormData({ ...formData, cost: Number(e.target.value) })} className="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-ctdi-blue outline-none" /></div>
                  </div>
                  <div className="flex gap-3 justify-end mt-4">
                    <button onClick={() => { setEditingId(null); setFormData({}); }} className="px-4 py-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 font-medium text-sm">Cancelar</button>
                    <button onClick={handleSave} className="flex items-center gap-2 px-6 py-2 bg-ctdi-blue hover:bg-blue-700 text-white rounded-lg shadow-md transition-all text-sm font-semibold"><Save size={16} /> Salvar</button>
                  </div>
                </div>
              )}
              {/* Table List */}
              <div className="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                <table className="w-full text-left border-collapse">
                  <thead><tr className="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 font-semibold"><th className="p-4">Data</th><th className="p-4">Unidade</th><th className="p-4">Volume (m³)</th><th className="p-4">Custo (R$)</th><th className="p-4 text-right">Ações</th></tr></thead>
                  <tbody className="divide-y divide-slate-100 dark:divide-slate-700">
                    {filteredManageData.map(record => (
                      <tr key={record.id} className="hover:bg-blue-50/50 dark:hover:bg-slate-700/50 transition-colors group">
                        <td className="p-4 text-sm font-medium text-slate-700 dark:text-slate-300">{new Date(record.date).toLocaleDateString('pt-BR')}</td>
                        <td className="p-4 text-sm text-slate-600 dark:text-slate-400">{record.unit}</td>
                        <td className="p-4 text-sm text-slate-700 dark:text-slate-300 font-mono">{record.volume.toLocaleString()}</td>
                        <td className="p-4 text-sm text-slate-700 dark:text-slate-300 font-mono">R$ {record.cost.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}</td>
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

export default WaterDashboard;