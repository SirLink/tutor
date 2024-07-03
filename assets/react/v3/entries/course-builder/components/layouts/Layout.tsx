import { colorTokens, containerMaxWidth, headerHeight } from '@Config/styles';
import Header from '@CourseBuilderComponents/layouts/Header';
import { CourseNavigatorProvider } from '@CourseBuilderContexts/CourseNavigatorContext';
import { type CourseFormData, courseDefaultData, useCourseDetailsQuery } from '@CourseBuilderServices/course';
import { convertCourseDataToFormData } from '@CourseBuilderUtils/utils';
import { useFormWithGlobalError } from '@Hooks/useFormWithGlobalError';
import { css } from '@emotion/react';
import { useEffect } from 'react';
import { FormProvider } from 'react-hook-form';
import { Outlet } from 'react-router-dom';

const Layout = () => {
  const params = new URLSearchParams(window.location.search);
  const courseId = params.get('course_id');

  const form = useFormWithGlobalError<CourseFormData>({
    defaultValues: courseDefaultData,
  });

  const courseDetailsQuery = useCourseDetailsQuery(Number(courseId));

  useEffect(() => {
    if (courseDetailsQuery.data) {
      form.reset.call(null, convertCourseDataToFormData(courseDetailsQuery.data));
    }
  }, [courseDetailsQuery.data, form.reset]);

  return (
    <FormProvider {...form}>
      <CourseNavigatorProvider>
        <div css={styles.wrapper}>
          <Header />
          <div css={styles.contentWrapper}>
            {/* Placeholder div for allocating the 1fr space */}
            <div />

            <Outlet />

            {/* Placeholder div for allocating the 1fr space */}
            <div />
          </div>
          {/* <Notebook /> */}
        </div>
      </CourseNavigatorProvider>
    </FormProvider>
  );
};

export default Layout;

const styles = {
  wrapper: css`
    background-color: ${colorTokens.surface.courseBuilder};
  `,
  contentWrapper: css`
    display: grid;
    grid-template-columns: 1fr ${containerMaxWidth}px 1fr;
    min-height: calc(100vh - ${headerHeight}px);
  `,
};
