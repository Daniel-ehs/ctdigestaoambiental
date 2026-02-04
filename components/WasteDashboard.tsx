

import React, { useMemo, useState, useRef, useCallback } from 'react';
import {
  PieChart, Pie, Cell, ResponsiveContainer, BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, AreaChart, Area, ReferenceLine, Sector
} from 'recharts';
import { Trash2, Recycle, DollarSign, Sparkles, Leaf, Edit2, Plus, X, Table, Search, Save, Filter, Camera, Download, Loader2, AlertTriangle, Upload } from 'lucide-react';
import { WasteRecord, UserRole } from '../types';
import { MetricCard } from './Metrics';
import { generateInsights } from '../services/geminiService';
import html2canvas from 'html2canvas';
import * as XLSX from 'xlsx';
import { api } from '../services/api';

interface Props {
  data: WasteRecord[];
  goal: number; // Recycling Rate Goal
  onUpdate: (record: WasteRecord) => void;
  onAdd: (record: WasteRecord) => void;
  onDelete: (id: string) => void;
  userRole: UserRole;
  theme: 'light' | 'dark';
}

const WasteDashboard: React.FC<Props> = ({ data, goal, onUpdate, onAdd, onDelete, userRole, theme }) => {
  const currentYear = new Date().getFullYear().toString();
  const [selectedYear, setSelectedYear] = useState<string>(currentYear);
  const [selectedType, setSelectedType] = useState<string>('All');
  const [insights, setInsights] = useState<string | null>(null);
  const [loadingInsights, setLoadingInsights] = useState(false);
  const [isExporting, setIsExporting] = useState(false);
  const [isImporting, setIsImporting] = useState(false);
  const fileInputRef = useRef<HTMLInputElement>(null);

  // Pie Chart Active State
  const [activeIndex, setActiveIndex] = useState(0);

  // Chart Refs
  const dashboardRef = useRef<HTMLDivElement>(null);
  const chartRef1 = useRef<HTMLDivElement>(null);
  const chartRef2 = useRef<HTMLDivElement>(null);
  const chartRef3 = useRef<HTMLDivElement>(null);

  // Modal State
  const [isManageModalOpen, setIsManageModalOpen] = useState(false);
  const [editingId, setEditingId] = useState<string | null>(null);
  const [formData, setFormData] = useState<Partial<WasteRecord>>({});
  const [deleteConfirmationId, setDeleteConfirmationId] = useState<string | null>(null);

  // Scroll Ref for Edit Form
  const formRef = useRef<HTMLDivElement>(null);

  // Modal Filter State
  const [modalSearchTerm, setModalSearchTerm] = useState('');

  // Colors
  const TYPE_COLORS: Record<string, string> = {
    'Papelão (Caixas)': '#3b82f6', // Blue
    'Plastico': '#ef4444', // Red
    'Madeira': '#f59e0b', // Amber
    'Eletronica': '#8b5cf6', // Violet
    'Ferro': '#64748b', // Slate
    'Não Reciclaveis': '#1f2937', // Dark Gray
  };

  // Calculate available years from data + current year
  const availableYears = useMemo(() => {
    const years = new Set<string>();
    years.add(currentYear);
    data.forEach(d => years.add(d.date.substring(0, 4)));
    return Array.from(years).sort().reverse();
  }, [data, currentYear]);

  const filteredData = useMemo(() => {
    return data.filter(d => {
      const matchYear = d.date.startsWith(selectedYear);
      const matchType = selectedType === 'All' || d.type === selectedType;
      return matchYear && matchType;
    });
  }, [data, selectedYear, selectedType]);

  // Aggregate Stats
  const stats = useMemo(() => {
    const totalWeight = filteredData.reduce((sum, r) => sum + r.weight, 0);
    const recyclable = filteredData.filter(r => r.category === 'Reciclável').reduce((sum, r) => sum + r.weight, 0);
    const nonRecyclable = filteredData.filter(r => r.category === 'Não Reciclável').reduce((sum, r) => sum + r.weight, 0);
    const recyclingRate = totalWeight > 0 ? (recyclable / totalWeight) * 100 : 0;
    const financial = filteredData.reduce((sum, r) => sum + r.financial, 0);

    return { totalWeight, recyclable, nonRecyclable, recyclingRate, financial };
  }, [filteredData]);

  // Chart Data: Monthly Evolution by Type (Stacked)
  const monthlyChartData = useMemo(() => {
    const months = Array.from(new Set(filteredData.map(d => d.date.substring(0, 7)))).sort();
    return months.map(month => {
      const records = filteredData.filter(d => d.date.startsWith(month));
      const dateObj = new Date(month + '-01T12:00:00');
      const displayMonth = dateObj.toLocaleString('pt-BR', { month: 'short' }).toUpperCase();

      const types: Record<string, number> = {};
      let monthlyFinancial = 0;
      records.forEach(r => {
        types[r.type] = (types[r.type] || 0) + r.weight;
        monthlyFinancial += r.financial;
      });

      return {
        month: displayMonth,
        fullDate: dateObj.toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' }),
        ...types,
        totalFinancial: monthlyFinancial
      };
    });
  }, [filteredData]);

  // Pie/Donut Data
  const pieData = useMemo(() => {
    const typeWeights: Record<string, number> = {};
    filteredData.forEach(d => {
      typeWeights[d.type] = (typeWeights[d.type] || 0) + d.weight;
    });

    return Object.keys(typeWeights)
      .map(type => ({
        name: type,
        value: typeWeights[type],
        color: TYPE_COLORS[type] || '#ccc'
      }))
      .sort((a, b) => b.value - a.value);
  }, [filteredData]);

  const typeData = useMemo(() => {
    const types: Record<string, number> = {};
    filteredData.forEach(d => {
      types[d.type] = (types[d.type] || 0) + d.weight;
    });
    return Object.keys(types).map(k => ({ name: k, weight: types[k] })).sort((a, b) => b.weight - a.weight);
  }, [filteredData]);

  const handleAnalyze = async () => {
    setLoadingInsights(true);
    const context = JSON.stringify({ stats, typeBreakdown: typeData.slice(0, 5) });
    const result = await generateInsights(context, `Gestão de Resíduos (Filtro: ${selectedType === 'All' ? 'Geral' : selectedType}) para o ano ${selectedYear}`);
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
        link.download = `Relatorio_Residuos_${selectedType}_${selectedYear}.png`;
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

  const handleExportExcel = () => {
    // Format data for Excel
    const excelData = filteredData.map(record => ({
      Data: new Date(record.date).toLocaleDateString('pt-BR'),
      Tipo: record.type,
      Categoria: record.category,
      'Peso (kg)': record.weight,
      'Preço Unit. (R$/kg)': record.pricePerKg,
      'Total (R$)': record.financial
    }));

    const ws = XLSX.utils.json_to_sheet(excelData);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, "Resíduos");
    XLSX.writeFile(wb, `Residuos_${selectedYear}.xlsx`);
  };

  const handleImportClick = () => {
    fileInputRef.current?.click();
  };

  const handleFileUpload = async (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0];
    if (!file) return;

    setIsImporting(true);
    const reader = new FileReader();

    reader.onload = async (evt) => {
      try {
        const bstr = evt.target?.result;
        const wb = XLSX.read(bstr, { type: 'binary' });
        const wsname = wb.SheetNames[0];
        const ws = wb.Sheets[wsname];
        const data = XLSX.utils.sheet_to_json(ws) as any[];

        // Map Excel columns back to App logic
        const formattedData: WasteRecord[] = data.map((row: any) => {
          // Basic mapping logic - adjust based on expected Excel format
          // Expecting: Data, Tipo, Categoria, Peso (kg), Preço Unit. (R$/kg), Total (R$)
          // OR standard keys if they use the template.
          // Let's try to parse standardized Date
          let dateStr = row['Data'] || row['date'];
          if (typeof dateStr === 'number') {
            // Excel date serial
            dateStr = new Date(Math.round((dateStr - 25569) * 86400 * 1000)).toISOString().split('T')[0];
          } else if (dateStr && dateStr.includes('/')) {
            const parts = dateStr.split('/');
            // pt-BR: DD/MM/YYYY -> YYYY-MM-DD
            if (parts.length === 3) dateStr = `${parts[2]}-${parts[1]}-${parts[0]}`;
          }

          return {
            id: crypto.randomUUID(), // Temp ID
            date: dateStr || new Date().toISOString().split('T')[0],
            type: row['Tipo'] || row['type'] || 'Outros',
            category: (row['Categoria'] || row['category']) === 'Não Reciclável' ? 'Não Reciclável' : 'Reciclável',
            weight: Number(row['Peso (kg)'] || row['weight']) || 0,
            financial: Number(row['Total (R$)'] || row['financial']) || 0,
            pricePerKg: Number(row['Preço Unit. (R$/kg)'] || row['pricePerKg']) || 0
          };
        });

        if (formattedData.length > 0) {
          if (confirm(`Encontrados ${formattedData.length} registros. Deseja importar?`)) {
            await api.createWasteBulk(formattedData);
            // Refresh parent
            // Ideally we should call a refresh function passed via props, but checking App structure 'onAdd' adds one.
            // We'll need to trigger a full refresh. 
            // Since 'onAdd' is single, we might need a hack or reload.
            // For now, let's call onAdd for the last one just to trigger something or alert user to refresh.
            // BETTER: The user refreshes manually or we reload.
            alert('Importação concluída com sucesso! Atualize a página para ver os dados.');
          }
        } else {
          alert('Nenhum dado válido encontrado na planilha.');
        }

      } catch (error) {
        console.error("Import error:", error);
        alert("Erro ao processar arquivo Excel.");
      } finally {
        setIsImporting(false);
        if (fileInputRef.current) fileInputRef.current.value = '';
      }
    };
    reader.readAsBinaryString(file);
  };

  // Effect to scroll to form when editingId changes
  React.useEffect(() => {
    if (editingId && formRef.current) {
      formRef.current.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  }, [editingId]);

  // --- CRUD Operations ---
  const handleEditClick = (record: WasteRecord) => {
    setEditingId(record.id);
    setFormData({
      ...record,
      pricePerKg: record.pricePerKg || (record.weight > 0 ? record.financial / record.weight : 0)
    });
  };

  const handleAddNewClick = () => {
    setEditingId('NEW');
    setFormData({
      date: new Date().toISOString().split('T')[0],
      type: 'Papelão (Caixas)',
      category: 'Reciclável',
      weight: 0,
      financial: 0,
      pricePerKg: 0
    });
  };

  const handleTypeChange = (newType: string) => {
    let newCategory: 'Reciclável' | 'Não Reciclável' = 'Reciclável';
    if (newType === 'Não Reciclaveis') {
      newCategory = 'Não Reciclável';
    }
    setFormData(prev => ({
      ...prev,
      type: newType,
      category: newCategory
    }));
  };

  const handleWeightChange = (val: number) => {
    setFormData(prev => {
      let newPrice = prev.pricePerKg;
      if (prev.financial && val > 0) {
        newPrice = prev.financial / val;
      } else if (prev.pricePerKg && prev.financial) {
        if (prev.financial) newPrice = prev.financial / val;
      }
      return { ...prev, weight: val, pricePerKg: newPrice };
    });
  };

  const handleFinancialChange = (val: number) => {
    setFormData(prev => ({
      ...prev,
      financial: val,
      pricePerKg: prev.weight && prev.weight > 0 ? val / prev.weight : 0
    }));
  };

  const handlePriceChange = (val: number) => {
    setFormData(prev => ({
      ...prev,
      pricePerKg: val,
      financial: (prev.weight || 0) * val
    }));
  };

  const handleSave = () => {
    if (!formData.date || !formData.type || !formData.category) return;

    const recordData: WasteRecord = {
      id: editingId === 'NEW' ? crypto.randomUUID() : editingId!,
      date: formData.date,
      type: formData.type,
      category: formData.category as 'Reciclável' | 'Não Reciclável',
      weight: Number(formData.weight) || 0,
      financial: Number(formData.financial) || 0,
      pricePerKg: Number(formData.pricePerKg) || 0
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
    setDeleteConfirmationId(id);
  };

  const confirmDelete = () => {
    if (deleteConfirmationId) {
      onDelete(deleteConfirmationId);
      setDeleteConfirmationId(null);
    }
  };

  const filteredManageData = useMemo(() => {
    return data.filter(d => {
      const searchLower = modalSearchTerm.toLowerCase();
      const matchSearch = modalSearchTerm === '' ||
        d.date.includes(searchLower) ||
        d.type.toLowerCase().includes(searchLower) ||
        d.category.toLowerCase().includes(searchLower);
      return matchSearch;
    }).sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime());
  }, [data, modalSearchTerm]);


  const chartColors = {
    grid: theme === 'dark' ? '#334155' : '#e2e8f0',
    text: theme === 'dark' ? '#94a3b8' : '#64748b',
  };

  const CustomTooltip = ({ active, payload, label }: any) => {
    if (active && payload && payload.length) {
      // Calculate total for stack
      const total = payload.reduce((sum: number, entry: any) => sum + (typeof entry.value === 'number' ? entry.value : 0), 0);

      return (
        <div className="bg-white/95 dark:bg-slate-800/95 backdrop-blur-sm p-3 border border-slate-100 dark:border-slate-700 rounded-xl shadow-xl z-50">
          <p className="text-xs font-bold text-slate-700 dark:text-slate-200 mb-2 border-b border-slate-100 dark:border-slate-700 pb-1">{label}</p>
          <div className="space-y-1">
            {payload.map((entry: any, index: number) => (
              <div key={index} className="flex items-center justify-between gap-4 text-xs">
                <span className="flex items-center gap-1.5 text-slate-500 dark:text-slate-400">
                  <div className="w-1.5 h-1.5 rounded-full" style={{ backgroundColor: entry.color }} />
                  {entry.name}:
                </span>
                <span className="font-mono font-bold text-slate-700 dark:text-slate-200">
                  {entry.name === 'Total (R$)'
                    ? `R$ ${entry.value.toLocaleString('pt-BR', { maximumFractionDigits: 0 })}`
                    : `${entry.value.toLocaleString()} kg`
                  }
                </span>
              </div>
            ))}
            {/* Show Total if it's the stacked chart */}
            {payload.length > 1 && payload[0].dataKey !== 'totalFinancial' && (
              <div className="pt-1 mt-1 border-t border-slate-100 dark:border-slate-700 flex justify-between gap-4 text-xs font-bold text-slate-800 dark:text-white">
                <span>Total:</span>
                <span>{total.toLocaleString()} kg</span>
              </div>
            )}
          </div>
        </div>
      );
    }
    return null;
  };

  // Active Shape for Donut Chart
  const renderActiveShape = (props: any) => {
    const RADIAN = Math.PI / 180;
    const { cx, cy, midAngle, innerRadius, outerRadius, startAngle, endAngle, fill, payload, percent, value } = props;
    const sin = Math.sin(-RADIAN * midAngle);
    const cos = Math.cos(-RADIAN * midAngle);
    const sx = cx + (outerRadius + 8) * cos;
    const sy = cy + (outerRadius + 8) * sin;
    const mx = cx + (outerRadius + 20) * cos;
    const my = cy + (outerRadius + 20) * sin;
    const ex = mx + (cos >= 0 ? 1 : -1) * 15;
    const ey = my;
    const textAnchor = cos >= 0 ? 'start' : 'end';

    return (
      <g>
        <Sector
          cx={cx}
          cy={cy}
          innerRadius={innerRadius}
          outerRadius={outerRadius + 5}
          startAngle={startAngle}
          endAngle={endAngle}
          fill={fill}
          stroke="#fff"
          strokeWidth={2}
        />
        <path d={`M${sx},${sy}L${mx},${my}L${ex},${ey}`} stroke={fill} fill="none" />
        <circle cx={ex} cy={ey} r={2} fill={fill} stroke="none" />
        <text x={ex + (cos >= 0 ? 1 : -1) * 8} y={ey} textAnchor={textAnchor} fill={theme === 'dark' ? '#fff' : '#333'} fontSize={12} fontWeight="bold">{`${value.toLocaleString()} kg`}</text>
        <text x={ex + (cos >= 0 ? 1 : -1) * 8} y={ey} dy={14} textAnchor={textAnchor} fill="#999" fontSize={10}>
          {`(${(percent * 100).toFixed(0)}%)`}
        </text>
      </g>
    );
  };

  const onPieEnter = useCallback((_: any, index: number) => {
    setActiveIndex(index);
  }, []);

  return (
    <>
      <div ref={dashboardRef} className="space-y-6 animate-fade-in relative pb-10">
        {/* Controls */}
        <div className="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-slate-800 p-5 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700">
          <div className="flex items-center gap-4">
            <div className="flex items-center justify-center w-12 h-12 rounded-xl bg-green-50 dark:bg-green-900/30 text-ctdi-green dark:text-green-400">
              <Trash2 size={24} strokeWidth={2.5} />
            </div>
            <div>
              <h2 className="text-xl font-bold text-slate-800 dark:text-white">Gestão de Resíduos</h2>
              <p className="text-sm text-slate-500 dark:text-slate-400 font-medium">Controle de geração e reciclagem</p>
            </div>
          </div>

          <div className="flex flex-wrap gap-3">
            <select
              value={selectedType}
              onChange={(e) => setSelectedType(e.target.value)}
              className="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-sm font-semibold text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-ctdi-blue/20 cursor-pointer"
            >
              <option value="All">Todos os Tipos</option>
              {Object.keys(TYPE_COLORS).map(type => (
                <option key={type} value={type}>{type}</option>
              ))}
            </select>

            <select
              value={selectedYear}
              onChange={(e) => setSelectedYear(e.target.value)}
              className="px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-sm font-semibold text-slate-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-ctdi-blue/20 cursor-pointer"
            >
              {availableYears.map(year => (
                <option key={year} value={year}>{year}</option>
              ))}
            </select>

            {userRole === 'manager' && (
              <button
                onClick={() => setIsManageModalOpen(true)}
                className="no-export flex items-center gap-2 px-5 py-2.5 bg-slate-800 dark:bg-slate-700 text-white rounded-xl shadow-lg hover:bg-slate-700 dark:hover:bg-slate-600 transition-all font-medium text-sm"
              >
                <Edit2 size={16} />
                Gerenciar Dados
              </button>
            )}

            <button
              onClick={handleAnalyze}
              disabled={loadingInsights}
              className="no-export flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-green-500 to-teal-600 text-white rounded-xl shadow-lg hover:shadow-green-200 dark:hover:shadow-none transition-all disabled:opacity-70 font-medium text-sm"
            >
              <Sparkles size={16} />
              {loadingInsights ? 'Analisando...' : 'IA Insights'}
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

          {/* Hidden Import Input */}
          <input
            type="file"
            accept=".xlsx, .xls"
            ref={fileInputRef}
            onChange={handleFileUpload}
            className="hidden"
          />

          {userRole === 'manager' && (
            <div className="flex gap-2">
              <button
                onClick={handleImportClick}
                disabled={isImporting}
                className="no-export flex items-center gap-2 px-5 py-2.5 bg-emerald-600 text-white rounded-xl shadow-lg hover:bg-emerald-700 transition-all font-medium text-sm"
                title="Importar Excel"
              >
                <Upload size={16} />
                {isImporting ? '...' : 'Importar'}
              </button>
              <button
                onClick={handleExportExcel}
                className="no-export flex items-center gap-2 px-5 py-2.5 bg-green-700 text-white rounded-xl shadow-lg hover:bg-green-800 transition-all font-medium text-sm"
                title="Exportar para Excel"
              >
                <Table size={16} />
                Excel
              </button>
            </div>
          )}
        </div>

        {insights && (
          <div className="bg-green-50 dark:bg-green-900/30 border border-green-100 dark:border-green-800 p-6 rounded-xl animate-fade-in relative">
            <button onClick={() => setInsights(null)} className="no-export absolute top-4 right-4 text-green-600 hover:text-green-800 dark:hover:text-green-300">×</button>
            <h4 className="flex items-center gap-2 font-bold text-green-800 dark:text-green-300 mb-2">
              <Sparkles size={18} /> Análise Inteligente
            </h4>
            <div className="prose prose-sm text-green-900 dark:text-green-200 max-w-none whitespace-pre-line">
              {insights}
            </div>
          </div>
        )}

        {/* Metrics Row */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <MetricCard title="Resíduos Totais" value={`${(stats.totalWeight / 1000).toFixed(2)} t`} icon={Trash2} color="slate" />

          {/* GOAL CARD */}
          <div className="relative overflow-hidden rounded-2xl p-6 text-white shadow-lg bg-gradient-to-br from-emerald-500 to-teal-600 shadow-emerald-200 dark:shadow-none flex flex-col justify-between group">
            <div className="absolute -right-6 -top-6 opacity-10 transition-transform duration-500 group-hover:scale-110 group-hover:rotate-12 pointer-events-none">
              <Recycle size={140} />
            </div>

            <div className="relative z-10">
              <div className="flex justify-between items-start mb-2">
                <div className="flex items-center gap-2 mb-2 opacity-90">
                  <div className="p-1.5 rounded-lg bg-white/20 backdrop-blur-sm">
                    <Recycle size={18} />
                  </div>
                  <p className="text-xs font-bold uppercase tracking-wider">Meta de Reciclagem</p>
                </div>
              </div>

              <div className="mb-4">
                <div className="flex items-baseline gap-2">
                  <h3 className="text-3xl font-bold text-white">{stats.recyclingRate.toFixed(1)}%</h3>
                  <span className="text-sm font-medium text-white/80">Reciclados</span>
                </div>
              </div>

              <div>
                <div className="relative h-3 bg-black/20 rounded-full overflow-hidden mb-2">
                  <div
                    className={`absolute top-0 left-0 h-full transition-all duration-1000 ${stats.recyclingRate >= goal ? 'bg-emerald-300' : 'bg-rose-300'}`}
                    style={{ width: `${Math.min(stats.recyclingRate, 100)}%` }}
                  ></div>
                  <div className="absolute top-0 bottom-0 w-0.5 bg-white z-10 shadow-[0_0_10px_rgba(255,255,255,0.8)]" style={{ left: `${goal}%` }}></div>
                </div>

                <div className="flex justify-between text-[10px] font-bold text-white/60 uppercase tracking-wider">
                  <span>0%</span>
                  <span className={stats.recyclingRate >= goal ? "text-emerald-300" : "text-rose-300"}>
                    {stats.recyclingRate >= goal ? 'Meta Atingida' : 'Abaixo da Meta'} ({goal}%)
                  </span>
                  <span>100%</span>
                </div>
              </div>
            </div>
          </div>

          <MetricCard title="Massa Reciclada" value={`${(stats.recyclable / 1000).toFixed(2)} t`} icon={Leaf} color="blue" />
          <MetricCard title="Resultado Financeiro" value={`R$ ${stats.financial.toLocaleString('pt-BR', { maximumFractionDigits: 0 })}`} icon={DollarSign} color={stats.financial >= 0 ? "violet" : "red"} />
        </div>

        {/* Charts Layout - Updated Grid */}
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">

          {/* Chart 1: Monthly Evolution Stacked Bar (Full Width) */}
          <div ref={chartRef1} className="lg:col-span-2 bg-white dark:bg-slate-800/50 dark:backdrop-blur-sm p-6 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 h-[450px] flex flex-col relative group">
            <div className="mb-6 flex justify-between items-start">
              <div>
                <h3 className="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                  <Trash2 size={18} className="text-ctdi-blue" />
                  Evolução de Resíduos por Tipo (kg)
                </h3>
                <p className="text-sm text-slate-400 dark:text-slate-500">Visualização detalhada da composição mensal.</p>
              </div>
              <button onClick={() => handleDownloadChart(chartRef1, 'evolucao-residuos')} className="no-export p-2 text-slate-400 hover:text-ctdi-blue hover:bg-blue-50 dark:hover:bg-slate-700 rounded-lg transition-colors opacity-0 group-hover:opacity-100">
                <Camera size={20} />
              </button>
            </div>
            <div className="flex-1 w-full min-h-0">
              <ResponsiveContainer width="100%" height="100%">
                <BarChart data={monthlyChartData} margin={{ top: 10, right: 10, left: 0, bottom: 0 }}>
                  <CartesianGrid strokeDasharray="3 3" vertical={false} stroke={chartColors.grid} />
                  <XAxis dataKey="month" stroke={chartColors.text} fontSize={11} tickLine={false} axisLine={false} dy={10} />
                  <YAxis stroke={chartColors.text} fontSize={11} tickLine={false} axisLine={false} tickFormatter={(v) => `${(v / 1000).toFixed(1)}t`} />
                  <Tooltip content={<CustomTooltip />} cursor={{ fill: theme === 'dark' ? '#334155' : '#f8fafc' }} />
                  <Legend iconType="circle" wrapperStyle={{ paddingTop: '20px', fontSize: '11px' }} />

                  {Object.keys(TYPE_COLORS).map((type, index) => (
                    <Bar
                      key={type}
                      dataKey={type}
                      stackId="a"
                      fill={TYPE_COLORS[type]}
                      barSize={45}
                      stroke={theme === 'dark' ? '#1e293b' : '#fff'}
                      strokeWidth={1}
                    />
                  ))}
                </BarChart>
              </ResponsiveContainer>
            </div>
          </div>

          {/* Row 2: Financial Evolution + Composition */}

          {/* Chart 2: Financial Evolution Area */}
          <div ref={chartRef2} className="bg-white dark:bg-slate-800/50 dark:backdrop-blur-sm p-6 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 h-[450px] flex flex-col relative group">
            <div className="mb-6 flex justify-between items-start">
              <div>
                <h3 className="text-lg font-bold text-slate-800 dark:text-white flex items-center gap-2">
                  <DollarSign size={18} className="text-emerald-500" />
                  Evolução Financeira (R$)
                </h3>
                <p className="text-sm text-slate-400 dark:text-slate-500">Receita mensal obtida com a venda de recicláveis.</p>
              </div>
              <button onClick={() => handleDownloadChart(chartRef2, 'evolucao-financeira-residuos')} className="no-export p-2 text-slate-400 hover:text-ctdi-blue hover:bg-blue-50 dark:hover:bg-slate-700 rounded-lg transition-colors opacity-0 group-hover:opacity-100">
                <Camera size={20} />
              </button>
            </div>
            <div className="flex-1 w-full min-h-0">
              <ResponsiveContainer width="100%" height="100%">
                <AreaChart data={monthlyChartData} margin={{ top: 10, right: 10, left: 0, bottom: 0 }}>
                  <defs>
                    <linearGradient id="colorFinancial" x1="0" y1="0" x2="0" y2="1">
                      <stop offset="5%" stopColor="#8b5cf6" stopOpacity={0.6} />
                      <stop offset="95%" stopColor="#8b5cf6" stopOpacity={0} />
                    </linearGradient>
                  </defs>
                  <CartesianGrid strokeDasharray="3 3" vertical={false} stroke={chartColors.grid} />
                  <XAxis dataKey="month" stroke={chartColors.text} fontSize={11} tickLine={false} axisLine={false} dy={10} />
                  <YAxis stroke={chartColors.text} fontSize={11} tickLine={false} axisLine={false} tickFormatter={(v) => `R$${(v / 1000).toFixed(0)}k`} />
                  <Tooltip content={<CustomTooltip />} />
                  <Area type="monotone" dataKey="totalFinancial" name="Total (R$)" stroke="#8b5cf6" strokeWidth={3} fill="url(#colorFinancial)" />
                </AreaChart>
              </ResponsiveContainer>
            </div>
          </div>

          {/* Chart 3: Composition Donut Chart - Improved */}
          <div ref={chartRef3} className="bg-white dark:bg-slate-800/50 dark:backdrop-blur-sm p-6 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 h-[450px] relative group flex flex-col">
            <div className="flex justify-between items-start mb-4">
              <div>
                <h3 className="text-lg font-bold text-slate-800 dark:text-white">Composição de Resíduos</h3>
                <p className="text-sm text-slate-400 dark:text-slate-500">Distribuição total por tipo.</p>
              </div>
              <button onClick={() => handleDownloadChart(chartRef3, 'composicao-residuos')} className="no-export p-2 text-slate-400 hover:text-ctdi-blue hover:bg-blue-50 dark:hover:bg-slate-700 rounded-lg transition-colors opacity-0 group-hover:opacity-100">
                <Camera size={20} />
              </button>
            </div>
            <div className="flex-1 w-full min-h-0">
              {pieData.length > 0 ? (
                <ResponsiveContainer width="100%" height="100%">
                  <PieChart>
                    <Pie
                      activeIndex={activeIndex}
                      activeShape={renderActiveShape}
                      data={pieData}
                      cx="50%"
                      cy="50%"
                      innerRadius={70}
                      outerRadius={95}
                      dataKey="value"
                      onMouseEnter={onPieEnter}
                      paddingAngle={3}
                      stroke="none"
                    >
                      {pieData.map((entry, index) => (
                        <Cell key={`cell-${index}`} fill={entry.color} stroke="none" />
                      ))}
                    </Pie>
                    <Legend
                      layout="vertical"
                      verticalAlign="middle"
                      align="right"
                      wrapperStyle={{ fontSize: '11px', fontWeight: 600, color: theme === 'dark' ? '#cbd5e1' : '#475569' }}
                    />
                  </PieChart>
                </ResponsiveContainer>
              ) : (
                <div className="h-full flex items-center justify-center text-slate-400 text-sm">Sem dados para este filtro.</div>
              )}
            </div>
          </div>
        </div>

        {/* Manage Modal */}
        {isManageModalOpen && (
          <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-sm animate-fade-in">
            <div className="bg-white dark:bg-slate-800 w-full max-w-6xl h-[85vh] rounded-2xl shadow-2xl flex flex-col overflow-hidden border border-slate-200 dark:border-slate-700">
              <div className="p-6 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between bg-white dark:bg-slate-800">
                <div>
                  <h2 className="text-xl font-bold text-slate-800 dark:text-white flex items-center gap-2"><Table className="text-ctdi-green" size={20} /> Gerenciamento de Resíduos</h2>
                  <p className="text-sm text-slate-500 dark:text-slate-400">Adicione, edite ou remova registros de coleta.</p>
                </div>
                <button onClick={() => setIsManageModalOpen(false)} className="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 transition-colors"><X size={20} /></button>
              </div>
              <div className="flex-1 overflow-auto bg-slate-50/50 dark:bg-slate-900/50 p-6">
                {!editingId && (
                  <div className="mb-6 flex flex-col md:flex-row gap-4 justify-between items-center bg-white dark:bg-slate-800 p-4 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700">
                    <div className="flex flex-1 items-center gap-4 w-full">
                      <div className="relative flex-1 max-w-md"><Search className="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" size={18} /><input type="text" placeholder="Buscar..." value={modalSearchTerm} onChange={(e) => setModalSearchTerm(e.target.value)} className="w-full pl-10 pr-4 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white text-sm outline-none focus:ring-2 focus:ring-ctdi-blue" /></div>
                    </div>
                    <button onClick={handleAddNewClick} className="flex items-center gap-2 px-4 py-2 bg-ctdi-green hover:bg-green-600 text-white rounded-lg shadow-sm transition-all text-sm font-semibold whitespace-nowrap"><Plus size={16} /> Adicionar Registro</button>
                  </div>
                )}
                {editingId && (
                  <div ref={formRef} className="mb-6 bg-white dark:bg-slate-800 p-6 rounded-xl border border-blue-200 dark:border-slate-600 shadow-sm animate-fade-in-up">
                    <h3 className="text-sm font-bold text-slate-800 dark:text-white uppercase tracking-wider mb-4 border-b border-slate-100 dark:border-slate-700 pb-2">{editingId === 'NEW' ? 'Novo Registro' : 'Editando Registro'}</h3>
                    <div className="grid grid-cols-1 md:grid-cols-6 gap-4">
                      <div><label className="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Data</label><input type="date" value={formData.date || ''} onChange={e => setFormData({ ...formData, date: e.target.value })} className="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-ctdi-blue outline-none" /></div>
                      <div><label className="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Tipo</label><select value={formData.type || 'Papelão (Caixas)'} onChange={e => handleTypeChange(e.target.value)} className="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-ctdi-blue outline-none">{Object.keys(TYPE_COLORS).map(type => (<option key={type} value={type}>{type}</option>))}</select></div>
                      <div><label className="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Categoria</label><select value={formData.category || 'Reciclável'} onChange={e => setFormData({ ...formData, category: e.target.value as any })} disabled className="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 text-sm focus:ring-2 focus:ring-ctdi-blue outline-none cursor-not-allowed"><option value="Reciclável">Reciclável</option><option value="Não Reciclável">Não Reciclável</option></select></div>
                      <div><label className="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Peso (kg)</label><input type="number" value={formData.weight} onChange={e => handleWeightChange(Number(e.target.value))} className="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-ctdi-blue outline-none" /></div>
                      <div><label className="block text-xs font-semibold text-emerald-600 dark:text-emerald-400 mb-1">Preço Unit. (R$/kg)</label><input type="number" step="0.0001" value={formData.pricePerKg} onChange={e => handlePriceChange(Number(e.target.value))} className="w-full px-3 py-2 rounded-lg border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/20 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-emerald-500 outline-none" /></div>
                      <div><label className="block text-xs font-semibold text-slate-500 dark:text-slate-400 mb-1">Total (R$)</label><input type="number" step="0.01" value={formData.financial} onChange={e => handleFinancialChange(Number(e.target.value))} className="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white text-sm focus:ring-2 focus:ring-ctdi-blue outline-none" /></div>
                    </div>
                    <div className="flex gap-3 justify-end mt-4">
                      <button onClick={() => { setEditingId(null); setFormData({}); }} className="px-4 py-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 font-medium text-sm">Cancelar</button>
                      <button onClick={handleSave} className="flex items-center gap-2 px-6 py-2 bg-ctdi-blue hover:bg-blue-700 text-white rounded-lg shadow-md transition-all text-sm font-semibold"><Save size={16} /> Salvar</button>
                    </div>
                  </div>
                )}
                <div className="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
                  <table className="w-full text-left border-collapse">
                    <thead><tr className="bg-slate-50 dark:bg-slate-700/50 border-b border-slate-200 dark:border-slate-700 text-xs uppercase tracking-wider text-slate-500 dark:text-slate-400 font-semibold"><th className="p-4">Data</th><th className="p-4">Tipo</th><th className="p-4">Categoria</th><th className="p-4">Peso (kg)</th><th className="p-4">R$/kg</th><th className="p-4">Total (R$)</th><th className="p-4 text-right">Ações</th></tr></thead>
                    <tbody className="divide-y divide-slate-100 dark:divide-slate-700">
                      {filteredManageData.map(record => (
                        <tr key={record.id} className="hover:bg-blue-50/50 dark:hover:bg-slate-700/50 transition-colors group">
                          <td className="p-4 text-sm font-medium text-slate-700 dark:text-slate-300">{new Date(record.date).toLocaleDateString('pt-BR')}</td>
                          <td className="p-4 text-sm text-slate-700 dark:text-slate-300">{record.type}</td>
                          <td className="p-4 text-sm"><span className={`px-2 py-1 rounded text-xs font-bold ${record.category === 'Reciclável' ? 'bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300' : 'bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400'}`}>{record.category}</span></td>
                          <td className="p-4 text-sm text-slate-700 dark:text-slate-300 font-mono">{record.weight.toLocaleString()}</td>
                          <td className="p-4 text-sm text-slate-500 dark:text-slate-400 font-mono text-xs">{record.pricePerKg ? `R$ ${record.pricePerKg.toFixed(4)}` : '-'}</td>
                          <td className={`p-4 text-sm font-mono font-bold ${record.financial >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500 dark:text-rose-400'}`}>R$ {record.financial.toLocaleString('pt-BR', { minimumFractionDigits: 2 })}</td>
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

      </div >

      {/* Delete Confirmation Modal */}
      {
        deleteConfirmationId && (
          <div className="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm animate-fade-in">
            <div className="bg-white dark:bg-slate-800 w-full max-w-md rounded-2xl shadow-2xl overflow-hidden border border-slate-200 dark:border-slate-700 p-6">
              <div className="flex flex-col items-center text-center">
                <div className="w-16 h-16 bg-rose-100 dark:bg-rose-900/30 rounded-full flex items-center justify-center mb-4 text-rose-600 dark:text-rose-500">
                  <AlertTriangle size={32} strokeWidth={2.5} />
                </div>
                <h3 className="text-xl font-bold text-slate-800 dark:text-white mb-2">Confirmar Exclusão</h3>
                <p className="text-slate-500 dark:text-slate-400 mb-6">
                  Esta ação <span className="font-bold text-rose-600 dark:text-rose-400">não pode ser desfeita</span>.
                  Tem certeza que deseja remover este registro permanentemente?
                </p>
                <div className="flex gap-3 w-full">
                  <button
                    onClick={() => setDeleteConfirmationId(null)}
                    className="flex-1 py-3 px-4 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 font-bold rounded-xl transition-all"
                  >
                    Cancelar
                  </button>
                  <button
                    onClick={confirmDelete}
                    className="flex-1 py-3 px-4 bg-rose-500 hover:bg-rose-600 text-white font-bold rounded-xl shadow-lg shadow-rose-200 dark:shadow-none transition-all"
                  >
                    Sim, Excluir
                  </button>
                </div>
              </div>
            </div>
          </div>
        )
      }
    </>
  );
};

export default WasteDashboard;