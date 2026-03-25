
import React, { useState, useEffect, useRef } from 'react';
import { Sparkles, X, Send, MessageSquare, Bot, User, Loader2, Minimize2, Maximize2 } from 'lucide-react';

interface Message {
  role: 'user' | 'assistant';
  content: string;
  timestamp: Date;
}

interface AIChatProps {
  activeModule: string;
  moduleData: any;
  theme: 'light' | 'dark';
}

const AIChat: React.FC<AIChatProps> = ({ activeModule, moduleData, theme }) => {
  const [isOpen, setIsOpen] = useState(false);
  const [messages, setMessages] = useState<Message[]>([]);
  const [input, setInput] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const [isMinimized, setIsMinimized] = useState(false);
  const messagesEndRef = useRef<HTMLDivElement>(null);

  const scrollToBottom = () => {
    messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
  };

  useEffect(() => {
    if (isOpen) {
      scrollToBottom();
    }
  }, [messages, isOpen]);

  const handleSend = async (e?: React.FormEvent) => {
    e?.preventDefault();
    if (!input.trim() || isLoading) return;

    const userMessage: Message = {
      role: 'user',
      content: input,
      timestamp: new Date()
    };

    setMessages(prev => [...prev, userMessage]);
    setInput('');
    setIsLoading(true);

    try {
      const response = await fetch('/api/ai-insights', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          context: JSON.stringify(moduleData),
          moduleName: activeModule,
          prompt: input // Adding user prompt to context if needed
        })
      });

      const data = await response.json();
      
      const assistantMessage: Message = {
        role: 'assistant',
        content: data.text || "Desculpe, não consegui processar sua solicitação.",
        timestamp: new Date()
      };

      setMessages(prev => [...prev, assistantMessage]);
    } catch (error) {
      console.error("AI Chat Error:", error);
      setMessages(prev => [...prev, {
        role: 'assistant',
        content: "Ocorreu um erro ao conectar com o serviço de IA.",
        timestamp: new Date()
      }]);
    } finally {
      setIsLoading(false);
    }
  };

  const getModuleDisplayName = (mod: string) => {
    switch (mod) {
      case 'electricity': return 'Energia Elétrica';
      case 'water': return 'Recursos Hídricos';
      case 'waste': return 'Gestão de Resíduos';
      default: return 'Geral';
    }
  };

  if (!isOpen) {
    return (
      <button
        onClick={() => setIsOpen(true)}
        className="fixed bottom-6 right-6 w-14 h-14 bg-gradient-to-br from-ctdi-blue to-blue-600 text-white rounded-full shadow-2xl flex items-center justify-center hover:scale-110 active:scale-95 transition-all z-50 group overflow-hidden"
        title="EcoTrack AI Chat"
      >
        <div className="absolute inset-0 bg-white/20 opacity-0 group-hover:opacity-100 transition-opacity animate-pulse"></div>
        <Sparkles size={24} className="relative z-10" />
      </button>
    );
  }

  return (
    <div className={`fixed bottom-6 right-6 z-50 flex flex-col transition-all duration-300 ${isMinimized ? 'h-14 w-64' : 'h-[500px] w-[380px]'} max-w-[calc(100vw-48px)] max-h-[calc(100vh-48px)]`}>
      {/* Container with Glassmorphism */}
      <div className="flex-1 flex flex-col bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 dark:border-slate-700/50 overflow-hidden animate-fade-in-up">
        
        {/* Header */}
        <div className="p-4 bg-gradient-to-r from-ctdi-blue to-blue-600 text-white flex items-center justify-between shrink-0">
          <div className="flex items-center gap-3">
            <div className="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-md">
              <Bot size={20} />
            </div>
            <div>
              <h3 className="text-sm font-bold leading-none">EcoTrack AI</h3>
              <p className="text-[10px] text-blue-100 mt-1 flex items-center gap-1">
                <span className="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                Analista de {getModuleDisplayName(activeModule)}
              </p>
            </div>
          </div>
          <div className="flex items-center gap-1">
            <button 
              onClick={() => setIsMinimized(!isMinimized)}
              className="p-1.5 hover:bg-white/10 rounded-lg transition-colors"
            >
              {isMinimized ? <Maximize2 size={16} /> : <Minimize2 size={16} />}
            </button>
            <button 
              onClick={() => setIsOpen(false)}
              className="p-1.5 hover:bg-white/10 rounded-lg transition-colors"
            >
              <X size={18} />
            </button>
          </div>
        </div>

        {!isMinimized && (
          <>
            {/* Messages Area */}
            <div className="flex-1 overflow-y-auto p-4 space-y-4 scrollbar-thin scrollbar-thumb-slate-200 dark:scrollbar-thumb-slate-700">
              {messages.length === 0 && (
                <div className="h-full flex flex-col items-center justify-center text-center p-6 gap-4">
                  <div className="w-16 h-16 rounded-3xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400">
                    <MessageSquare size={32} />
                  </div>
                  <div>
                    <p className="text-sm font-bold text-slate-700 dark:text-slate-200">Como posso ajudar hoje?</p>
                    <p className="text-xs text-slate-500 dark:text-slate-400 mt-1">Pergunte sobre tendências, anomalias ou dicas de sustentabilidade.</p>
                  </div>
                  <div className="grid grid-cols-1 gap-2 w-full mt-2">
                    {[
                      "Quais os principais insights deste mês?",
                      "Houve alguma anomalia no consumo?",
                      "Sugestões para reduzir custos."
                    ].map((hint, i) => (
                      <button 
                        key={i} 
                        onClick={() => { setInput(hint); }}
                        className="text-xs p-2.5 bg-slate-50 dark:bg-slate-800/50 hover:bg-blue-50 dark:hover:bg-blue-900/20 text-slate-600 dark:text-slate-300 rounded-xl border border-slate-100 dark:border-slate-700 transition-colors text-left font-medium"
                      >
                        {hint}
                      </button>
                    ))}
                  </div>
                </div>
              )}
              
              {messages.map((msg, i) => (
                <div key={i} className={`flex ${msg.role === 'user' ? 'justify-end' : 'justify-start'}`}>
                  <div className={`max-w-[85%] flex gap-2 ${msg.role === 'user' ? 'flex-row-reverse' : 'flex-row'}`}>
                    <div className={`shrink-0 w-8 h-8 rounded-full flex items-center justify-center ${msg.role === 'user' ? 'bg-slate-200 dark:bg-slate-700 text-slate-600 dark:text-slate-300' : 'bg-blue-100 dark:bg-blue-900/50 text-ctdi-blue dark:text-blue-400'}`}>
                      {msg.role === 'user' ? <User size={16} /> : <Bot size={16} />}
                    </div>
                    <div className={`p-3 rounded-2xl text-sm ${
                      msg.role === 'user' 
                        ? 'bg-ctdi-blue text-white rounded-tr-none shadow-md shadow-blue-100 dark:shadow-none' 
                        : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 border border-slate-100 dark:border-slate-700 rounded-tl-none shadow-sm'
                    }`}>
                      <p className="whitespace-pre-line leading-relaxed">{msg.content}</p>
                      <p className={`text-[9px] mt-1.5 opacity-50 ${msg.role === 'user' ? 'text-right' : 'text-left'}`}>
                        {msg.timestamp.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                      </p>
                    </div>
                  </div>
                </div>
              ))}
              {isLoading && (
                <div className="flex justify-start">
                  <div className="bg-slate-100 dark:bg-slate-800 p-3 rounded-2xl rounded-tl-none flex items-center gap-2">
                    <Loader2 size={14} className="animate-spin text-slate-400" />
                    <span className="text-xs text-slate-400 font-medium">EcoTrack está pensando...</span>
                  </div>
                </div>
              )}
              <div ref={messagesEndRef} />
            </div>

            {/* Input Area */}
            <form onSubmit={handleSend} className="p-4 bg-white dark:bg-slate-800 border-t border-slate-100 dark:border-slate-700 shrink-0">
              <div className="relative">
                <input
                  type="text"
                  value={input}
                  onChange={(e) => setInput(e.target.value)}
                  placeholder="Digite sua dúvida..."
                  className="w-full pl-4 pr-12 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-ctdi-blue focus:border-transparent transition-all"
                />
                <button
                  type="submit"
                  disabled={!input.trim() || isLoading}
                  className="absolute right-1.5 top-1/2 -translate-y-1/2 w-9 h-9 flex items-center justify-center bg-ctdi-blue text-white rounded-xl hover:bg-blue-600 active:scale-95 transition-all disabled:opacity-50"
                >
                  <Send size={18} />
                </button>
              </div>
              <p className="text-[10px] text-center text-slate-400 mt-2">
                Respostas geradas por IA podem conter imprecisões.
              </p>
            </form>
          </>
        )}
      </div>
    </div>
  );
};

export default AIChat;
