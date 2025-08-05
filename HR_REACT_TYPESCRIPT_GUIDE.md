# 🚀 HR MODULE: REACT + TYPESCRIPT MIGRATION GUIDE

## 📁 **ESTRUTURA DO PROJETO**
```
src/
├── components/hr/
│   ├── employees/
│   ├── payroll/
│   ├── attendance/
│   └── shared/
├── hooks/hr/
├── services/hr/
├── types/hr/
├── stores/hr/
└── utils/hr/
```

## 🎯 **TIPOS TYPESCRIPT**

### **Interfaces Principais**
```typescript
// types/hr/employee.ts
export interface Employee {
  id: number;
  full_name: string;
  email: string;
  department_id: number;
  position_id: number;
  basic_salary: number;
  status: 'active' | 'inactive';
  hire_date: string;
  department?: Department;
  position?: JobPosition;
}

export interface Department {
  id: number;
  name: string;
  description?: string;
}

export interface Payroll {
  id: number;
  employee_id: number;
  basic_salary: number;
  gross_salary: number;
  net_salary: number;
  status: 'draft' | 'approved' | 'paid';
  payment_date: string;
  employee?: Employee;
  payroll_items?: PayrollItem[];
}

export interface PayrollItem {
  id: number;
  payroll_id: number;
  type: 'earning' | 'deduction' | 'bonus';
  name: string;
  amount: number;
  is_taxable: boolean;
}
```

## ⚛️ **COMPONENTES PRINCIPAIS**

### **1. EmployeesPage**
```typescript
// components/hr/employees/EmployeesPage.tsx
import React from 'react';
import { useEmployees } from '@/hooks/hr/useEmployees';
import { EmployeeTable } from './EmployeeTable';
import { EmployeeModal } from './EmployeeModal';

export function EmployeesPage() {
  const {
    data,
    isLoading,
    pagination,
    updatePagination,
    openCreateModal,
    openEditModal,
    openDeleteModal,
    isModalOpen,
    selectedEmployee,
    closeModal,
    handleSave,
    handleDelete
  } = useEmployees();

  const columns = [
    { key: 'full_name', header: 'Nome', sortable: true },
    { key: 'email', header: 'Email', sortable: true },
    { 
      key: 'department', 
      header: 'Departamento',
      render: (emp: Employee) => emp.department?.name || '-'
    },
    {
      key: 'basic_salary',
      header: 'Salário Base',
      render: (emp: Employee) => `${emp.basic_salary.toLocaleString()} AOA`
    }
  ];

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <h1 className="text-2xl font-bold">Gestão de Funcionários</h1>
        <button
          onClick={openCreateModal}
          className="bg-blue-600 text-white px-4 py-2 rounded-lg"
        >
          Adicionar Funcionário
        </button>
      </div>

      <EmployeeTable
        data={data}
        columns={columns}
        loading={isLoading}
        pagination={pagination}
        onPaginationChange={updatePagination}
        onEdit={openEditModal}
        onDelete={openDeleteModal}
      />

      <EmployeeModal
        open={isModalOpen}
        onClose={closeModal}
        employee={selectedEmployee}
        onSave={handleSave}
      />
    </div>
  );
}
```

### **2. PayrollPage**
```typescript
// components/hr/payroll/PayrollPage.tsx
import React from 'react';
import { usePayroll } from '@/hooks/hr/usePayroll';
import { PayrollTable } from './PayrollTable';
import { PayrollProcessModal } from './PayrollProcessModal';

export function PayrollPage() {
  const {
    data,
    isLoading,
    isProcessModalOpen,
    selectedEmployee,
    payrollData,
    openProcessModal,
    closeProcessModal,
    handleEmployeeSelect,
    handlePayrollSave
  } = usePayroll();

  return (
    <div className="space-y-6">
      <div className="flex justify-between items-center">
        <h1 className="text-2xl font-bold">Folha de Pagamento</h1>
        <button
          onClick={openProcessModal}
          className="bg-green-600 text-white px-4 py-2 rounded-lg"
        >
          Processar Folha
        </button>
      </div>

      <PayrollTable
        data={data}
        loading={isLoading}
      />

      <PayrollProcessModal
        open={isProcessModalOpen}
        onClose={closeProcessModal}
        employee={selectedEmployee}
        payrollData={payrollData}
        onEmployeeSelect={handleEmployeeSelect}
        onSave={handlePayrollSave}
      />
    </div>
  );
}
```

## 🎣 **HOOKS CUSTOMIZADOS**

### **1. useEmployees Hook**
```typescript
// hooks/hr/useEmployees.ts
import { useState } from 'react';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { employeeService } from '@/services/hr/employeeService';
import { Employee, PaginationParams } from '@/types/hr';

export function useEmployees() {
  const [pagination, setPagination] = useState<PaginationParams>({
    page: 1,
    per_page: 15,
    search: '',
    sort_by: 'created_at'
  });

  const [isModalOpen, setIsModalOpen] = useState(false);
  const [selectedEmployee, setSelectedEmployee] = useState<Employee | null>(null);

  const queryClient = useQueryClient();

  // Query para listar funcionários
  const { data, isLoading } = useQuery({
    queryKey: ['employees', pagination],
    queryFn: () => employeeService.getEmployees(pagination)
  });

  // Mutation para criar/atualizar
  const saveMutation = useMutation({
    mutationFn: ({ id, data }: { id?: number; data: Partial<Employee> }) =>
      id ? employeeService.updateEmployee(id, data) : employeeService.createEmployee(data),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['employees'] });
      closeModal();
    }
  });

  // Mutation para deletar
  const deleteMutation = useMutation({
    mutationFn: employeeService.deleteEmployee,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['employees'] });
    }
  });

  const updatePagination = (newParams: Partial<PaginationParams>) => {
    setPagination(prev => ({ ...prev, ...newParams }));
  };

  const openCreateModal = () => {
    setSelectedEmployee(null);
    setIsModalOpen(true);
  };

  const openEditModal = (employee: Employee) => {
    setSelectedEmployee(employee);
    setIsModalOpen(true);
  };

  const closeModal = () => {
    setIsModalOpen(false);
    setSelectedEmployee(null);
  };

  const handleSave = (data: Partial<Employee>) => {
    saveMutation.mutate({
      id: selectedEmployee?.id,
      data
    });
  };

  const handleDelete = (id: number) => {
    deleteMutation.mutate(id);
  };

  return {
    data,
    isLoading,
    pagination,
    updatePagination,
    isModalOpen,
    selectedEmployee,
    openCreateModal,
    openEditModal,
    closeModal,
    handleSave,
    handleDelete,
    isSubmitting: saveMutation.isPending || deleteMutation.isPending
  };
}
```

### **2. usePayroll Hook**
```typescript
// hooks/hr/usePayroll.ts
import { useState } from 'react';
import { useMutation, useQueryClient } from '@tanstack/react-query';
import { payrollService } from '@/services/hr/payrollService';
import { Employee, PayrollFormData } from '@/types/hr';

export function usePayroll() {
  const [isProcessModalOpen, setIsProcessModalOpen] = useState(false);
  const [selectedEmployee, setSelectedEmployee] = useState<Employee | null>(null);
  const [payrollData, setPayrollData] = useState<any>(null);

  const queryClient = useQueryClient();

  // Query para listar folhas existentes
  const { data, isLoading } = useQuery({
    queryKey: ['payrolls'],
    queryFn: () => payrollService.getPayrolls({})
  });

  // Mutation para calcular componentes
  const calculateMutation = useMutation({
    mutationFn: ({ employeeId, periodId }: { employeeId: number; periodId: number }) =>
      payrollService.calculatePayrollComponents(employeeId, periodId),
    onSuccess: (data) => {
      setPayrollData(data);
    }
  });

  // Mutation para salvar folha
  const saveMutation = useMutation({
    mutationFn: payrollService.createPayroll,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['payrolls'] });
      closeProcessModal();
    }
  });

  const openProcessModal = () => {
    setIsProcessModalOpen(true);
  };

  const closeProcessModal = () => {
    setIsProcessModalOpen(false);
    setSelectedEmployee(null);
    setPayrollData(null);
  };

  const handleEmployeeSelect = (employee: Employee) => {
    setSelectedEmployee(employee);
    // Calcular componentes automaticamente
    calculateMutation.mutate({
      employeeId: employee.id,
      periodId: 1 // ID do período atual
    });
  };

  const handlePayrollSave = (formData: PayrollFormData) => {
    saveMutation.mutate(formData);
  };

  return {
    data,
    isLoading,
    isProcessModalOpen,
    selectedEmployee,
    payrollData,
    openProcessModal,
    closeProcessModal,
    handleEmployeeSelect,
    handlePayrollSave,
    isCalculating: calculateMutation.isPending,
    isSaving: saveMutation.isPending
  };
}
```

## 🌐 **SERVIÇOS DA API**

### **Employee Service**
```typescript
// services/hr/employeeService.ts
import { api } from '@/lib/api';
import { Employee, PaginatedResponse, PaginationParams } from '@/types/hr';

export const employeeService = {
  async getEmployees(params: PaginationParams): Promise<PaginatedResponse<Employee>> {
    const response = await api.get('/hr/employees', { params });
    return response.data;
  },

  async getEmployee(id: number): Promise<Employee> {
    const response = await api.get(`/hr/employees/${id}`);
    return response.data.data;
  },

  async createEmployee(data: Partial<Employee>): Promise<Employee> {
    const response = await api.post('/hr/employees', data);
    return response.data.data;
  },

  async updateEmployee(id: number, data: Partial<Employee>): Promise<Employee> {
    const response = await api.put(`/hr/employees/${id}`, data);
    return response.data.data;
  },

  async deleteEmployee(id: number): Promise<void> {
    await api.delete(`/hr/employees/${id}`);
  }
};
```

### **Payroll Service**
```typescript
// services/hr/payrollService.ts
export const payrollService = {
  async getPayrolls(params: PaginationParams): Promise<PaginatedResponse<Payroll>> {
    const response = await api.get('/hr/payrolls', { params });
    return response.data;
  },

  async createPayroll(data: PayrollFormData): Promise<Payroll> {
    const response = await api.post('/hr/payrolls', data);
    return response.data.data;
  },

  async calculatePayrollComponents(employeeId: number, periodId: number) {
    const response = await api.post('/hr/payrolls/calculate', {
      employee_id: employeeId,
      payroll_period_id: periodId
    });
    return response.data.data;
  },

  async approvePayroll(id: number): Promise<Payroll> {
    const response = await api.post(`/hr/payrolls/${id}/approve`);
    return response.data.data;
  }
};
```

## 🏪 **ESTADO GLOBAL (Zustand)**

```typescript
// stores/hr/hrStore.ts
import { create } from 'zustand';
import { Employee, Department } from '@/types/hr';

interface HRState {
  employees: Employee[];
  departments: Department[];
  selectedEmployee: Employee | null;
  
  setEmployees: (employees: Employee[]) => void;
  setDepartments: (departments: Department[]) => void;
  setSelectedEmployee: (employee: Employee | null) => void;
}

export const useHRStore = create<HRState>((set) => ({
  employees: [],
  departments: [],
  selectedEmployee: null,
  
  setEmployees: (employees) => set({ employees }),
  setDepartments: (departments) => set({ departments }),
  setSelectedEmployee: (employee) => set({ selectedEmployee: employee })
}));
```

## 📋 **FORMULÁRIOS COM REACT HOOK FORM + ZOD**

### **Employee Form**
```typescript
// components/hr/employees/EmployeeForm.tsx
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';

const employeeSchema = z.object({
  full_name: z.string().min(1, 'Nome é obrigatório'),
  email: z.string().email('Email inválido'),
  phone: z.string().min(1, 'Telefone é obrigatório'),
  department_id: z.number().min(1, 'Departamento é obrigatório'),
  basic_salary: z.number().min(0, 'Salário deve ser positivo')
});

type EmployeeFormData = z.infer<typeof employeeSchema>;

interface EmployeeFormProps {
  employee?: Employee;
  onSubmit: (data: EmployeeFormData) => void;
  isSubmitting?: boolean;
}

export function EmployeeForm({ employee, onSubmit, isSubmitting }: EmployeeFormProps) {
  const {
    register,
    handleSubmit,
    formState: { errors }
  } = useForm<EmployeeFormData>({
    resolver: zodResolver(employeeSchema),
    defaultValues: employee
  });

  return (
    <form onSubmit={handleSubmit(onSubmit)} className="space-y-4">
      <div>
        <label className="block text-sm font-medium">Nome Completo</label>
        <input
          {...register('full_name')}
          className="w-full border rounded-lg px-3 py-2"
        />
        {errors.full_name && (
          <p className="text-red-500 text-sm">{errors.full_name.message}</p>
        )}
      </div>

      <div>
        <label className="block text-sm font-medium">Email</label>
        <input
          type="email"
          {...register('email')}
          className="w-full border rounded-lg px-3 py-2"
        />
        {errors.email && (
          <p className="text-red-500 text-sm">{errors.email.message}</p>
        )}
      </div>

      <div>
        <label className="block text-sm font-medium">Salário Base</label>
        <input
          type="number"
          step="0.01"
          {...register('basic_salary', { valueAsNumber: true })}
          className="w-full border rounded-lg px-3 py-2"
        />
        {errors.basic_salary && (
          <p className="text-red-500 text-sm">{errors.basic_salary.message}</p>
        )}
      </div>

      <div className="flex justify-end space-x-2">
        <button
          type="submit"
          disabled={isSubmitting}
          className="bg-blue-600 text-white px-4 py-2 rounded-lg disabled:opacity-50"
        >
          {isSubmitting ? 'Salvando...' : 'Salvar'}
        </button>
      </div>
    </form>
  );
}
```

## 🎨 **COMPONENTES REUTILIZÁVEIS**

### **Data Table**
```typescript
// components/shared/DataTable.tsx
import React from 'react';

interface Column<T> {
  key: string;
  header: string;
  sortable?: boolean;
  render?: (item: T) => React.ReactNode;
}

interface DataTableProps<T> {
  data: T[];
  columns: Column<T>[];
  loading?: boolean;
  onEdit?: (item: T) => void;
  onDelete?: (item: T) => void;
}

export function DataTable<T extends { id: number }>({
  data,
  columns,
  loading,
  onEdit,
  onDelete
}: DataTableProps<T>) {
  if (loading) {
    return <div className="text-center py-8">Carregando...</div>;
  }

  return (
    <div className="overflow-x-auto">
      <table className="min-w-full bg-white border border-gray-200">
        <thead className="bg-gray-50">
          <tr>
            {columns.map((column) => (
              <th
                key={column.key}
                className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase"
              >
                {column.header}
              </th>
            ))}
            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
              Ações
            </th>
          </tr>
        </thead>
        <tbody className="divide-y divide-gray-200">
          {data.map((item) => (
            <tr key={item.id} className="hover:bg-gray-50">
              {columns.map((column) => (
                <td key={column.key} className="px-6 py-4 whitespace-nowrap">
                  {column.render 
                    ? column.render(item) 
                    : String((item as any)[column.key] || '')
                  }
                </td>
              ))}
              <td className="px-6 py-4 whitespace-nowrap">
                <div className="flex space-x-2">
                  {onEdit && (
                    <button
                      onClick={() => onEdit(item)}
                      className="text-blue-600 hover:text-blue-900"
                    >
                      Editar
                    </button>
                  )}
                  {onDelete && (
                    <button
                      onClick={() => onDelete(item)}
                      className="text-red-600 hover:text-red-900"
                    >
                      Excluir
                    </button>
                  )}
                </div>
              </td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}
```

---

## 📝 **RESUMO DE MIGRAÇÃO**

### **Equivalências Laravel → React**
- **Livewire Components** → **React Components + Hooks**
- **Blade Views** → **JSX Templates**
- **PHP Arrays** → **TypeScript Interfaces**
- **Laravel Validation** → **Zod + React Hook Form**
- **Session State** → **Zustand/Redux Store**
- **Eloquent Models** → **API Services + React Query**

### **Stack Recomendada**
- Next.js 14+ (App Router)
- TypeScript 5+
- TailwindCSS + Radix UI
- React Query + Zustand
- React Hook Form + Zod

**Este guia fornece a estrutura completa para recriar todo o módulo HR em React/TypeScript mantendo as mesmas funcionalidades e fluxos do sistema Laravel/Livewire original.**
