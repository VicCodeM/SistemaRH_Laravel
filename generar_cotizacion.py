# -*- coding: utf-8 -*-
from fpdf import FPDF
import datetime

class CotizacionPDF(FPDF):
    def header(self):
        self.set_fill_color(25, 40, 65)
        self.rect(0, 0, 210, 35, 'F')
        self.set_font('helvetica', 'B', 22)
        self.set_text_color(255, 255, 255)
        self.set_xy(15, 10)
        self.cell(0, 10, 'COTIZACION', new_x="LMARGIN", new_y="NEXT")
        self.set_font('helvetica', '', 11)
        self.set_xy(15, 18)
        self.cell(0, 6, 'Desarrollo de Sistema de Gestion de Recursos Humanos', new_x="LMARGIN", new_y="NEXT")
        self.set_xy(15, 24)
        self.set_text_color(200, 200, 200)
        self.cell(0, 6, 'Proyecto a la Medida | Entrega en Hosting Compartido del Cliente', new_x="LMARGIN", new_y="NEXT")
        self.ln(15)

    def footer(self):
        self.set_y(-15)
        self.set_font('helvetica', 'I', 8)
        self.set_text_color(128, 128, 128)
        self.cell(0, 10, f'Pagina {self.page_no()}/{{nb}}', align='C')
        self.set_x(-50)
        self.cell(0, 10, 'Documento valido por 15 dias naturales', align='R')

def crear_cotizacion():
    pdf = CotizacionPDF()
    pdf.alias_nb_pages()
    pdf.add_page()
    pdf.set_auto_page_break(auto=True, margin=20)

    pdf.set_font('helvetica', '', 10)
    pdf.set_text_color(80, 80, 80)
    pdf.cell(0, 6, f'Fecha: {datetime.datetime.now().strftime("%d de %B de %Y")}', align='R', new_x="LMARGIN", new_y="NEXT")
    pdf.cell(0, 6, 'Folio: SISTEMARH-2026-001', align='R', new_x="LMARGIN", new_y="NEXT")
    pdf.ln(5)

    pdf.set_font('helvetica', 'B', 12)
    pdf.set_text_color(25, 40, 65)
    pdf.cell(0, 8, 'CLIENTE:', new_x="LMARGIN", new_y="NEXT")
    pdf.set_font('helvetica', '', 10)
    pdf.set_text_color(60, 60, 60)
    pdf.cell(0, 6, '[Nombre de la empresa cliente]', new_x="LMARGIN", new_y="NEXT")
    pdf.cell(0, 6, '[Nombre del representante]', new_x="LMARGIN", new_y="NEXT")
    pdf.cell(0, 6, '[Ciudad, Estado]', new_x="LMARGIN", new_y="NEXT")
    pdf.ln(5)

    pdf.set_font('helvetica', 'B', 12)
    pdf.set_text_color(25, 40, 65)
    pdf.cell(0, 8, 'DESCRIPCION DEL PROYECTO:', new_x="LMARGIN", new_y="NEXT")
    pdf.set_font('helvetica', '', 10)
    pdf.set_text_color(60, 60, 60)
    desc = (
        "Desarrollo de un Sistema de Gestion de Recursos Humanos (SistemaRH) bajo pedido, "
        "construido con Laravel 13 + Livewire 3 + PHP 8.3. El sistema permite la operacion "
        "entre cuatro roles de usuario: Administrador, Empresa, Candidato e Interno. "
        "Incluye gestion de vacantes, postulaciones tipo Kanban, solicitud de empleo multipaso, "
        "chat interno, servicios asignados, catalogos dinamicos, reportes con exportacion CSV/PDF, "
        "busqueda global y panel administrativo completo."
    )
    pdf.multi_cell(0, 6, desc)
    pdf.ln(3)

    pdf.set_font('helvetica', 'B', 12)
    pdf.set_text_color(25, 40, 65)
    pdf.cell(0, 8, 'ALCANCE INCLUIDO:', new_x="LMARGIN", new_y="NEXT")
    pdf.set_font('helvetica', '', 10)
    pdf.set_text_color(60, 60, 60)

    modulos = [
        "Autenticacion multirol (Admin, Empresa, Candidato, Interno)",
        "Dashboard administrativo con KPIs, reportes y exportacion",
        "Gestion completa de Empresas (aprobar, suspender, editar, exportar)",
        "Gestion completa de Candidatos con solicitud de empleo multipaso (5 secciones)",
        "Modulo de Vacantes / Reclutamiento con matching inteligente de candidatos",
        "Postulaciones con tablero Kanban interactivo (Livewire)",
        "Servicios Asignados con Kanban, asignacion de personal y cambio de estados",
        "Solicitud de servicios por Empresa y por Candidato",
        "Catalogo de Opciones dinamico (grupos gestionables por el admin)",
        "Catalogo de Servicios con jerarquia de niveles",
        "Personal Externo e Interno con capacidades, CV y exportacion",
        "Chat interno en tiempo real (salas, mensajes, notificaciones)",
        "Configuracion del sistema, parametros y bitacora de actividad",
        "Busqueda global tipo Cmd+K",
        "CSS custom completo (~1,100 lineas), responsive y diseno profesional",
        "Instalacion en hosting compartido del cliente y capacitacion basica",
    ]

    for i, mod in enumerate(modulos, 1):
        pdf.set_x(15)
        y_start = pdf.get_y()
        pdf.cell(10, 6, f'{i}.', align='R')
        pdf.set_xy(25, y_start)
        pdf.multi_cell(0, 6, mod)

    pdf.ln(3)

    pdf.set_font('helvetica', 'B', 12)
    pdf.set_text_color(25, 40, 65)
    pdf.cell(0, 8, 'INVERSION:', new_x="LMARGIN", new_y="NEXT")

    pdf.set_fill_color(230, 235, 240)
    pdf.set_font('helvetica', 'B', 10)
    pdf.set_text_color(25, 40, 65)
    pdf.cell(100, 9, 'Concepto', border=1, align='L', fill=True)
    pdf.cell(40, 9, 'Cantidad', border=1, align='C', fill=True)
    pdf.cell(50, 9, 'Importe', border=1, align='R', fill=True)
    pdf.ln()

    pdf.set_font('helvetica', '', 10)
    pdf.set_text_color(60, 60, 60)

    filas = [
        ('Desarrollo de SistemaRH (modulos completos entregados)', '1 proyecto', '$60,000.00'),
        ('Adaptacion e instalacion en hosting compartido del cliente', '1 vez', '$3,000.00'),
        ('Capacitacion basica (1 sesion de 2 horas via videollamada)', '1 sesion', '$2,000.00'),
    ]

    for concepto, qty, precio in filas:
        pdf.cell(100, 8, concepto, border=1, align='L')
        pdf.cell(40, 8, qty, border=1, align='C')
        pdf.cell(50, 8, precio, border=1, align='R')
        pdf.ln()

    pdf.set_font('helvetica', 'B', 10)
    pdf.cell(140, 9, 'Subtotal', border=1, align='R')
    pdf.cell(50, 9, '$65,000.00', border=1, align='R')
    pdf.ln()

    pdf.set_font('helvetica', '', 10)
    pdf.cell(140, 8, 'IVA (16%)', border=1, align='R')
    pdf.cell(50, 8, '$10,400.00', border=1, align='R')
    pdf.ln()

    pdf.set_fill_color(25, 40, 65)
    pdf.set_text_color(255, 255, 255)
    pdf.set_font('helvetica', 'B', 12)
    pdf.cell(140, 11, 'TOTAL', border=1, align='R', fill=True)
    pdf.cell(50, 11, '$75,400.00 MXN', border=1, align='R', fill=True)
    pdf.ln()

    pdf.ln(8)

    pdf.set_text_color(25, 40, 65)
    pdf.set_font('helvetica', 'B', 12)
    pdf.cell(0, 8, 'FORMA DE PAGO:', new_x="LMARGIN", new_y="NEXT")
    pdf.set_font('helvetica', '', 10)
    pdf.set_text_color(60, 60, 60)
    pdf.cell(0, 6, '50% de anticipo al aceptar cotizacion:  $37,700.00 MXN (incluye IVA)', new_x="LMARGIN", new_y="NEXT")
    pdf.cell(0, 6, '50% contra entrega e instalacion:       $37,700.00 MXN (incluye IVA)', new_x="LMARGIN", new_y="NEXT")
    pdf.ln(5)

    pdf.set_font('helvetica', 'B', 12)
    pdf.set_text_color(25, 40, 65)
    pdf.cell(0, 8, 'CONDICIONES Y ENTREGABLES:', new_x="LMARGIN", new_y="NEXT")
    pdf.set_font('helvetica', '', 10)
    pdf.set_text_color(60, 60, 60)
    condiciones = [
        'El codigo fuente se entrega completo para instalacion en hosting compartido del cliente.',
        'El cliente debe proporcionar acceso FTP/cPanel y base de datos MySQL.',
        'Incluye garantia de 30 dias naturales por correccion de errores de funcionamiento.',
        'No incluye desarrollo de nuevos modulos post-entrega (se cotizan por separado).',
        'Soporte tecnico post-garantia: $800 MXN/hora o paquete mensual disponible.',
        'Tiempo estimado de instalacion y entrega: 3 a 5 dias habiles despues del anticipo.',
        'El sistema requiere PHP 8.3+ y extensiones estandar de Laravel (pdo, mbstring, openssl, etc.).',
    ]
    for cond in condiciones:
        pdf.set_x(15)
        y_start = pdf.get_y()
        pdf.cell(6, 6, '-')
        pdf.set_xy(21, y_start)
        pdf.multi_cell(0, 6, cond)

    pdf.ln(5)

    pdf.set_font('helvetica', 'B', 11)
    pdf.set_text_color(180, 60, 60)
    pdf.cell(0, 8, 'NOTA IMPORTANTE:', new_x="LMARGIN", new_y="NEXT")
    pdf.set_font('helvetica', '', 10)
    pdf.set_text_color(80, 80, 80)
    pdf.multi_cell(0, 6,
        'Esta cotizacion considera el estado actual del sistema (~85% funcional). '
        'Modulos documentados pero no implementados (Tickets/SLA, notificaciones por email '
        'completas, panel de bitacora extendido) pueden desarrollarse en una segunda fase '
        'con costo adicional a cotizar.'
    )

    pdf.ln(15)
    pdf.set_font('helvetica', 'B', 10)
    pdf.set_text_color(60, 60, 60)
    pdf.cell(95, 8, '_' * 40, align='C')
    pdf.cell(95, 8, '_' * 40, align='C')
    pdf.ln()
    pdf.cell(95, 6, 'EL DESARROLLADOR', align='C')
    pdf.cell(95, 6, 'EL CLIENTE', align='C')

    output_path = 'C:/Dev/Web/SistemaRH_Laravel/Cotizacion_SistemaRH.pdf'
    pdf.output(output_path)
    print(f'PDF generado exitosamente: {output_path}')

crear_cotizacion()
