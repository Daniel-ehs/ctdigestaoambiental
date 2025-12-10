
import React, { useState } from 'react';
import { Upload, FileText, CheckCircle, AlertCircle, Wand2, Download } from 'lucide-react';
import { parseNaturalLanguageImport } from '../services/geminiService';
import { AppState, ElectricityRecord, WaterRecord, WasteRecord } from '../types';

interface Props {
  onImport: (type: keyof AppState, data: any[]) => void;
}

const DataImport: React.FC<Props> = ({ onImport }) => {
  const [importType, setImportType] = useState<'electricity' | 'water' | 'waste'>('electricity');
  const [rawData, setRawData] = useState('');
  const [isProcessing, setIsProcessing] = useState(false);
  const [status, setStatus] = useState<{ type: 'success' | 'error', message: string } | null>(null);

  const handleSmartParse = async () => {
    if (!rawData.trim()) return;
    setIsProcessing(true);
    setStatus(null);

    try {
      const jsonStr = await parseNaturalLanguageImport(rawData, importType);
      const parsed = JSON.parse(jsonStr);
      
      if (Array.isArray(parsed) && parsed.length > 0) {
        // Enforce ID creation if missing
        const enriched = parsed.map(item => ({ ...item, id: crypto.randomUUID() }));
        onImport(importType, enriched);
        setStatus({ type: 'success', message: `Sucesso! ${enriched.length} registros foram importados.` });
        setRawData('');
      } else {
        setStatus({ type: 'error', message: 'IA retornou dados vazios ou inválidos. Verifique o texto.' });
      }
    } catch (e) {
      setStatus({ type: 'error', message: 'Falha ao processar dados. Verifique o formato.' });
    } finally {
      setIsProcessing(false);
    }
  };

  const handleDownloadTemplate = () => {
    let tableHeader = '';
    let tableRow = '';
    let filename = '';

    switch(importType) {
        case 'electricity':
            tableHeader = '<tr><th>Data (YYYY-MM-DD)</th><th>Unidade</th><th>Consumo CPFL (kWh)</th><th>Custo CPFL (R$)</th><th>Consumo Flora (kWh)</th><th>Custo Flora (R$)</th><th>Economia Flora (R$)</th></tr>';
            tableRow = '<tr><td>2024-01-01</td><td>Galpão 6</td><td>1585</td><td>1445.18</td><td>0</td><td>0</td><td>0</td></tr>';
            filename = 'modelo_energia.xls';
            break;
        case 'water':
            tableHeader = '<tr><th>Data (YYYY-MM-DD)</th><th>Unidade</th><th>Volume (m3)</th><th>Custo (R$)</th></tr>';
            tableRow = '<tr><td>2024-01-01</td><td>Galpão 6</td><td>38</td><td>2673.14</td></tr>';
            filename = 'modelo_agua.xls';
            break;
        case 'waste':
            tableHeader = '<tr><th>Data (YYYY-MM-DD)</th><th>Tipo</th><th>Categoria</th><th>Peso (kg)</th><th>Preço Unit (R$/kg)</th><th>Total (R$)</th></tr>';
            tableRow = '<tr><td>2025-01-01</td><td>Papelão (Caixas)</td><td>Reciclável</td><td>1663</td><td>0.48</td><td>798.24</td></tr>';
            filename = 'modelo_residuos.xls';
            break;
    }

    const tableHTML = `
      <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
      <head><meta charset="UTF-8"></head>
      <body>
        <table border="1">
          <thead>${tableHeader}</thead>
          <tbody>${tableRow}</tbody>
        </table>
      </body>
      </html>
    `;

    const blob = new Blob([tableHTML], { type: 'application/vnd.ms-excel' });
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  };

  const getImportLabel = (t: string) => {
      switch(t) {
          case 'electricity': return 'Eletricidade';
          case 'water': return 'Água';
          case 'waste': return 'Resíduos';
          default: return t;
      }
  }

  return (
    <div className="max-w-4xl mx-auto space-y-8 animate-fade-in">
       <div className="bg-gradient-to-br from-ctdi-blue to-blue-800 rounded-2xl p-8 text-white shadow-xl">
         <div className="flex items-start gap-6">
           <div className="p-4 bg-white/10 rounded-xl backdrop-blur-sm">
             <Upload size={32} />
           </div>
           <div>
             <h2 className="text-3xl font-bold mb-2">Migração de Dados Históricos</h2>
             <p className="text-blue-100 text-lg leading-relaxed max-w-2xl">
               INSTRUÇÃO CRÍTICA: Baixe o modelo abaixo, preencha com seus dados no Excel, copie as células da tabela e cole na caixa de texto.
               Nossa IA estruturará os dados automaticamente.
             </p>
           </div>
         </div>
       </div>

       <div className="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden">
         <div className="border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/50 px-6 py-4 flex flex-col md:flex-row justify-between items-center gap-4">
           <div className="flex gap-4 overflow-x-auto w-full md:w-auto pb-2 md:pb-0">
             {['electricity', 'water', 'waste'].map(t => (
               <button
                 key={t}
                 onClick={() => setImportType(t as any)}
                 className={`px-4 py-2 rounded-lg font-medium text-sm capitalize transition-all whitespace-nowrap ${
                   importType === t 
                     ? 'bg-white dark:bg-slate-700 text-ctdi-blue dark:text-blue-300 shadow-sm ring-1 ring-slate-200 dark:ring-slate-600' 
                     : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200'
                 }`}
               >
                 {getImportLabel(t)}
               </button>
             ))}
           </div>

           <button 
             onClick={handleDownloadTemplate}
             className="flex items-center gap-2 px-4 py-2 text-sm font-medium text-slate-600 dark:text-slate-300 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors shadow-sm whitespace-nowrap"
           >
             <Download size={16} />
             Baixar Modelo Excel (.xls)
           </button>
         </div>

         <div className="p-6">
            <label className="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
              Cole os dados da tabela Excel
            </label>
            <div className="relative">
              <textarea
                value={rawData}
                onChange={(e) => setRawData(e.target.value)}
                placeholder={importType === 'electricity'
                  ? `Cole aqui os dados copiados do Excel...\nExemplo:\n2024-01-01  Galpão 6  1585  1445.18...`
                  : importType === 'waste'
                  ? `Cole aqui os dados copiados do Excel...\nExemplo:\n2025-01-01  Papelão  Reciclável  1663...`
                  : `Cole aqui os dados copiados do Excel...\nExemplo:\n2024-01-01  Galpão 6  38  2673.14...`
                }
                className="w-full h-64 p-4 rounded-lg border border-slate-200 dark:border-slate-700 font-mono text-sm focus:ring-2 focus:ring-ctdi-blue focus:border-transparent resize-none bg-slate-50 dark:bg-slate-900/50 text-slate-900 dark:text-slate-100 placeholder-slate-400 dark:placeholder-slate-500"
              />
              <div className="absolute bottom-4 right-4 flex gap-2">
                <button
                  onClick={handleSmartParse}
                  disabled={isProcessing || !rawData}
                  className="flex items-center gap-2 px-6 py-2 bg-ctdi-green hover:bg-green-600 text-white rounded-lg shadow-md transition-all disabled:opacity-50 disabled:cursor-not-allowed font-medium"
                >
                  {isProcessing ? (
                    <span className="animate-spin">⏳</span>
                  ) : (
                    <Wand2 size={18} />
                  )}
                  {isProcessing ? 'Processando IA...' : 'Importar Dados'}
                </button>
              </div>
            </div>

            {status && (
              <div className={`mt-4 p-4 rounded-lg flex items-center gap-3 animate-fade-in ${
                status.type === 'success' ? 'bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300' : 'bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300'
              }`}>
                {status.type === 'success' ? <CheckCircle size={20} /> : <AlertCircle size={20} />}
                <span className="font-medium">{status.message}</span>
              </div>
            )}
         </div>
       </div>
    </div>
  );
};

export default DataImport;
